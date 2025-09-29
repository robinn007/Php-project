/**
 * @file VideoCallService.js
 * @description Service for managing WebRTC video calls (1-on-1 and group)
 */
angular.module('myApp').factory('VideoCallService', ['$rootScope', 'SocketService', 'AuthService', function($rootScope, SocketService, AuthService) {
    var peerConnections = {}; // { email: RTCPeerConnection }
    var localStream = null;
    var remoteStreams = {}; // { email: MediaStream }
    var isCallActive = false;
    var currentCallEmails = []; // Array of emails in current call
    var isGroupCall = false;
    var currentGroupId = null;
    
    // ICE servers configuration
    var iceServers = {
        iceServers: [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' }
        ]
    };
    
    function createPeerConnection(email) {
        var pc = new RTCPeerConnection(iceServers);
        
        // Handle ICE candidates
        pc.onicecandidate = function(event) {
            if (event.candidate) {
                console.log('Sending ICE candidate to', email);
                SocketService.emit('ice_candidate', {
                    sender_email: AuthService.getCurrentUserEmail(),
                    receiver_email: email,
                    group_id: currentGroupId,
                    candidate: event.candidate
                });
            }
        };
        
        // Handle remote stream
        pc.ontrack = function(event) {
            console.log('Received remote track from', email);
            if (!remoteStreams[email]) {
                remoteStreams[email] = new MediaStream();
            }
            remoteStreams[email].addTrack(event.track);
            $rootScope.$broadcast('remote_stream_added', { email: email, stream: remoteStreams[email] });
        };
        
        // Handle connection state changes
        pc.onconnectionstatechange = function() {
            console.log('Connection state with', email, ':', pc.connectionState);
            if (pc.connectionState === 'disconnected' || 
                pc.connectionState === 'failed' ||
                pc.connectionState === 'closed') {
                removePeerConnection(email);
            }
        };
        
        return pc;
    }
    
    function removePeerConnection(email) {
        if (peerConnections[email]) {
            peerConnections[email].close();
            delete peerConnections[email];
        }
        if (remoteStreams[email]) {
            delete remoteStreams[email];
        }
        currentCallEmails = currentCallEmails.filter(e => e !== email);
        $rootScope.$broadcast('peer_disconnected', email);
        
        if (currentCallEmails.length === 0 && isCallActive) {
            endCall();
        }
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
    
    // Start group video call
    async function startGroupCall(groupId, members) {
        try {
            isGroupCall = true;
            currentGroupId = groupId;
            currentCallEmails = members;
            isCallActive = true;
            
            // Get local stream
            localStream = await getLocalStream();
            $rootScope.$broadcast('local_stream_ready', localStream);
            
            console.log('Starting group call with members:', members);
            
            // Notify all members about the group call
            SocketService.emit('group_video_call_start', {
                caller_email: AuthService.getCurrentUserEmail(),
                caller_name: AuthService.getCurrentUser(),
                group_id: groupId,
                members: members
            });
            
            $rootScope.$broadcast('call_initiated', { groupId: groupId, members: members });
            
        } catch (error) {
            console.error('Error starting group call:', error);
            endCall();
            throw error;
        }
    }
    
    // Start 1-on-1 video call
    async function startCall(receiverEmail, receiverName) {
        try {
            isGroupCall = false;
            currentGroupId = null;
            currentCallEmails = [receiverEmail];
            isCallActive = true;
            
            // Get local stream
            localStream = await getLocalStream();
            $rootScope.$broadcast('local_stream_ready', localStream);
            
            // Create peer connection
            var pc = createPeerConnection(receiverEmail);
            peerConnections[receiverEmail] = pc;
            
            // Add local tracks to peer connection
            localStream.getTracks().forEach(track => {
                pc.addTrack(track, localStream);
            });
            
            // Create and send offer
            var offer = await pc.createOffer();
            await pc.setLocalDescription(offer);
            
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
    
    // Join group call
    async function joinGroupCall(groupId, members, initiatorEmail) {
        try {
            isGroupCall = true;
            currentGroupId = groupId;
            currentCallEmails = members.filter(e => e !== AuthService.getCurrentUserEmail());
            isCallActive = true;
            
            // Get local stream
            localStream = await getLocalStream();
            $rootScope.$broadcast('local_stream_ready', localStream);
            
            console.log('Joining group call, will connect to:', currentCallEmails);
            
            // Notify that we've joined
            SocketService.emit('group_video_call_joined', {
                joiner_email: AuthService.getCurrentUserEmail(),
                joiner_name: AuthService.getCurrentUser(),
                group_id: groupId
            });
            
            // Create offers to all existing members
            for (var email of currentCallEmails) {
                await createOfferToPeer(email);
            }
            
            $rootScope.$broadcast('call_answered');
            
        } catch (error) {
            console.error('Error joining group call:', error);
            endCall();
            throw error;
        }
    }
    
    async function createOfferToPeer(email) {
        var pc = createPeerConnection(email);
        peerConnections[email] = pc;
        
        // Add local tracks
        localStream.getTracks().forEach(track => {
            pc.addTrack(track, localStream);
        });
        
        // Create and send offer
        var offer = await pc.createOffer();
        await pc.setLocalDescription(offer);
        
        console.log('Sending peer offer to', email);
        SocketService.emit('video_call_offer', {
            caller_email: AuthService.getCurrentUserEmail(),
            receiver_email: email,
            caller_name: AuthService.getCurrentUser(),
            group_id: currentGroupId,
            offer: offer
        });
    }
    
    // Answer call (1-on-1 or from group member)
    async function answerCall(callerEmail, offer, groupId) {
        try {
            if (groupId) {
                currentGroupId = groupId;
                isGroupCall = true;
            }
            
            if (!currentCallEmails.includes(callerEmail)) {
                currentCallEmails.push(callerEmail);
            }
            
            // Get local stream if not already obtained
            if (!localStream) {
                localStream = await getLocalStream();
                $rootScope.$broadcast('local_stream_ready', localStream);
            }
            
            isCallActive = true;
            
            // Create peer connection
            var pc = createPeerConnection(callerEmail);
            peerConnections[callerEmail] = pc;
            
            // Add local tracks
            localStream.getTracks().forEach(track => {
                pc.addTrack(track, localStream);
            });
            
            // Set remote description
            await pc.setRemoteDescription(new RTCSessionDescription(offer));
            
            // Create and send answer
            var answer = await pc.createAnswer();
            await pc.setLocalDescription(answer);
            
            console.log('Sending call answer to', callerEmail);
            SocketService.emit('video_call_answer', {
                caller_email: callerEmail,
                answerer_email: AuthService.getCurrentUserEmail(),
                group_id: currentGroupId,
                answer: answer
            });
            
            $rootScope.$broadcast('call_answered');
            
        } catch (error) {
            console.error('Error answering call:', error);
            endCall();
            throw error;
        }
    }
    
    async function handleAnswer(email, answer) {
        try {
            var pc = peerConnections[email];
            if (pc) {
                await pc.setRemoteDescription(new RTCSessionDescription(answer));
                console.log('Remote description set for', email);
                $rootScope.$broadcast('call_connected', email);
            }
        } catch (error) {
            console.error('Error handling answer from', email, ':', error);
        }
    }
    
    async function handleIceCandidate(email, candidate) {
        try {
            var pc = peerConnections[email];
            if (pc) {
                await pc.addIceCandidate(new RTCIceCandidate(candidate));
                console.log('ICE candidate added for', email);
            }
        } catch (error) {
            console.error('Error adding ICE candidate for', email, ':', error);
        }
    }
    
    function endCall() {
        console.log('Ending call');
        
        // Notify others
        if (isGroupCall && currentGroupId) {
            SocketService.emit('group_video_call_left', {
                leaver_email: AuthService.getCurrentUserEmail(),
                group_id: currentGroupId
            });
        } else if (currentCallEmails.length > 0) {
            for (var email of currentCallEmails) {
                SocketService.emit('video_call_ended', {
                    sender_email: AuthService.getCurrentUserEmail(),
                    receiver_email: email
                });
            }
        }
        
        // Stop local stream
        if (localStream) {
            localStream.getTracks().forEach(track => track.stop());
            localStream = null;
        }
        
        // Close all peer connections
        for (var email in peerConnections) {
            peerConnections[email].close();
        }
        
        peerConnections = {};
        remoteStreams = {};
        isCallActive = false;
        currentCallEmails = [];
        isGroupCall = false;
        currentGroupId = null;
        
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
        startGroupCall: startGroupCall,
        joinGroupCall: joinGroupCall,
        answerCall: answerCall,
        handleAnswer: handleAnswer,
        handleIceCandidate: handleIceCandidate,
        endCall: endCall,
        rejectCall: rejectCall,
        toggleAudio: toggleAudio,
        toggleVideo: toggleVideo,
        isCallActive: function() { return isCallActive; },
        getCurrentCallEmails: function() { return currentCallEmails; },
        isGroupCall: function() { return isGroupCall; },
        getRemoteStreams: function() { return remoteStreams; }
    };
}]);