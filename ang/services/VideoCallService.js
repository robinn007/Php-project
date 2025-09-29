/**
 * @file VideoCallService.js
 * @description Service for managing WebRTC video calls
 */
angular.module('myApp').factory('VideoCallService', ['$rootScope', 'SocketService', 'AuthService', function($rootScope, SocketService, AuthService) {
    var peerConnection = null;
    var localStream = null;
    var remoteStream = null;
    var isCallActive = false;
    var currentCallEmail = null;
    
    // ICE servers configuration (using public STUN servers)
    var iceServers = {
        iceServers: [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' }
        ]
    };
    
    function createPeerConnection() {
        peerConnection = new RTCPeerConnection(iceServers);
        
        // Handle ICE candidates
        peerConnection.onicecandidate = function(event) {
            if (event.candidate && currentCallEmail) {
                console.log('Sending ICE candidate');
                SocketService.emit('ice_candidate', {
                    sender_email: AuthService.getCurrentUserEmail(),
                    receiver_email: currentCallEmail,
                    candidate: event.candidate
                });
            }
        };
        
        // Handle remote stream
        peerConnection.ontrack = function(event) {
            console.log('Received remote track');
            if (!remoteStream) {
                remoteStream = new MediaStream();
            }
            remoteStream.addTrack(event.track);
            $rootScope.$broadcast('remote_stream_added', remoteStream);
        };
        
        // Handle connection state changes
        peerConnection.onconnectionstatechange = function() {
            console.log('Connection state:', peerConnection.connectionState);
            if (peerConnection.connectionState === 'disconnected' || 
                peerConnection.connectionState === 'failed' ||
                peerConnection.connectionState === 'closed') {
                endCall();
            }
        };
        
        return peerConnection;
    }
    
    async function getLocalStream() {
        try {
            localStream = await navigator.mediaDevices.getUserMedia({
                video: true,
                audio: true
            });
            console.log('Local stream obtained');
            return localStream;
        } catch (error) {
            console.error('Error accessing media devices:', error);
            throw error;
        }
    }
    
    async function startCall(receiverEmail, receiverName) {
        try {
            currentCallEmail = receiverEmail;
            isCallActive = true;
            
            // Get local stream
            localStream = await getLocalStream();
            $rootScope.$broadcast('local_stream_ready', localStream);
            
            // Create peer connection
            peerConnection = createPeerConnection();
            
            // Add local tracks to peer connection
            localStream.getTracks().forEach(track => {
                peerConnection.addTrack(track, localStream);
            });
            
            // Create and send offer
            const offer = await peerConnection.createOffer();
            await peerConnection.setLocalDescription(offer);
            
            console.log('Sending call offer to', receiverEmail);
            SocketService.emit('video_call_offer', {
                caller_email: AuthService.getCurrentUserEmail(),
                receiver_email: receiverEmail,
                caller_name: AuthService.getCurrentUser(),
                offer: offer
            });
            
            $rootScope.$broadcast('call_initiated', { receiverEmail, receiverName });
            
        } catch (error) {
            console.error('Error starting call:', error);
            endCall();
            throw error;
        }
    }
    
    async function answerCall(callerEmail, offer) {
        try {
            currentCallEmail = callerEmail;
            isCallActive = true;
            
            // Get local stream
            localStream = await getLocalStream();
            $rootScope.$broadcast('local_stream_ready', localStream);
            
            // Create peer connection
            peerConnection = createPeerConnection();
            
            // Add local tracks
            localStream.getTracks().forEach(track => {
                peerConnection.addTrack(track, localStream);
            });
            
            // Set remote description
            await peerConnection.setRemoteDescription(new RTCSessionDescription(offer));
            
            // Create and send answer
            const answer = await peerConnection.createAnswer();
            await peerConnection.setLocalDescription(answer);
            
            console.log('Sending call answer to', callerEmail);
            SocketService.emit('video_call_answer', {
                caller_email: callerEmail,
                answerer_email: AuthService.getCurrentUserEmail(),
                answer: answer
            });
            
            $rootScope.$broadcast('call_answered');
            
        } catch (error) {
            console.error('Error answering call:', error);
            endCall();
            throw error;
        }
    }
    
    async function handleAnswer(answer) {
        try {
            await peerConnection.setRemoteDescription(new RTCSessionDescription(answer));
            console.log('Remote description set');
            $rootScope.$broadcast('call_connected');
        } catch (error) {
            console.error('Error handling answer:', error);
        }
    }
    
    async function handleIceCandidate(candidate) {
        try {
            if (peerConnection) {
                await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
                console.log('ICE candidate added');
            }
        } catch (error) {
            console.error('Error adding ICE candidate:', error);
        }
    }
    
    function endCall() {
        console.log('Ending call');
        
        if (currentCallEmail) {
            SocketService.emit('video_call_ended', {
                sender_email: AuthService.getCurrentUserEmail(),
                receiver_email: currentCallEmail
            });
        }
        
        // Stop local stream
        if (localStream) {
            localStream.getTracks().forEach(track => track.stop());
            localStream = null;
        }
        
        // Close peer connection
        if (peerConnection) {
            peerConnection.close();
            peerConnection = null;
        }
        
        remoteStream = null;
        isCallActive = false;
        currentCallEmail = null;
        
        $rootScope.$broadcast('call_ended');
    }
    
    function rejectCall(callerEmail) {
        SocketService.emit('video_call_rejected', {
            caller_email: callerEmail,
            receiver_email: AuthService.getCurrentUserEmail()
        });
        $rootScope.$broadcast('call_rejected');
    }
    
    function toggleAudio(enabled) {
        if (localStream) {
            localStream.getAudioTracks().forEach(track => {
                track.enabled = enabled;
            });
        }
    }
    
    function toggleVideo(enabled) {
        if (localStream) {
            localStream.getVideoTracks().forEach(track => {
                track.enabled = enabled;
            });
        }
    }
    
    return {
        startCall: startCall,
        answerCall: answerCall,
        handleAnswer: handleAnswer,
        handleIceCandidate: handleIceCandidate,
        endCall: endCall,
        rejectCall: rejectCall,
        toggleAudio: toggleAudio,
        toggleVideo: toggleVideo,
        isCallActive: function() { return isCallActive; },
        getCurrentCallEmail: function() { return currentCallEmail; }
    };
}]);