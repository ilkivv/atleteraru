

MobileWebrtc = function ()
{
	this.signalingLink = '/bitrix/components/bitrix/im.messenger/call.ajax.php';
	this.initiator = false;
	this.callUserId = 0;
	this.callBackUserId = 0;
	this.callChatId = 0;
	this.callToGroup = false;
	this.waitTimeout = false;
	this.callGroupUsers = [];
	this.callInit = false;
	this.callActive = false;
	this.ready = false;
	this.pcStart = {};
	this.connected = {};
	this.sessionDescription = {};
	this.remoteSessionDescription = {};
	this.iceCandidates = [];
	this.iceCandidatesToSend = [];
	this.iceCandidateTimeout = 0;
	this.peerConnectionInited = false;
	this.userId = BX.message('USER_ID');

	webrtc.setEventListeners(
		{
			//UI callbacks
			"onAnswer": BX.proxy(this.onAnswer, this),
			"onDecline": BX.proxy(this.onDecline, this),
			"onCallback": BX.proxy(this.onCallback, this),
			"onClose": BX.proxy(this.onClose, this),
			//WebRTC callbacks
			"onUserMediaSuccess": BX.proxy(this.onUserMediaSuccess, this),
			"onDisconnect": BX.proxy(this.onDisconnect, this),
			"onPeerConnectionCreated": BX.proxy(this.onPeerConnectionCreated, this),
			"onIceCandidateDiscovered": BX.proxy(this.onIceCandidateDiscovered, this),
			"onLocalSessionDescriptionCreated": BX.proxy(this.onLocalSessionDescriptionCreated, this),
			"onIceConnectionStateChanged": BX.proxy(this.onIceConnectionStateChanged, this),
			"onIceGatheringStateChanged": BX.proxy(this.onIceGatheringStateChanged, this),
			"onSignalingStateChanged": BX.proxy(this.onSignalingStateChanged, this),
			"onError": BX.proxy(this.onError, this)
		}
	);
};

MobileWebrtc.prototype.onDecline = function (params)
{
	this.callCommand(this.callChatId, 'decline');
	this.resetState();
};

MobileWebrtc.prototype.onAnswer = function (params)
{
	if(this.connected[this.userId] && this.callActive && this.callChatId == params.chatId)
	{
		webrtc.exec("destroyPeerConnection");
		this.callCommand(this.callChatId, "ready");
	}
	else
	{
		webrtc.UI.show(
			webrtc.UI.state.CONVERSATION
		);
		webrtc.getUserMedia();
	}
	this.waitTimeout = false;
	this.callToGroup = false;
	this.callChatId = params.chatId;
	this.callUserId = params.senderId;
	this.callActive = true;
	this.initiator = false;


	BX.ajax({
		url: this.signalingLink + '?CALL_ANSWER',
		method: 'POST',
		dataType: 'json',
		timeout: 30,
		data: {'MOBILE': 'Y', 'IM_CALL': 'Y', 'COMMAND': 'answer', 'CHAT_ID': this.callChatId, 'CALL_TO_GROUP': this.callToGroup ? 'Y' : 'N', 'RECIPIENT_ID': this.callUserId, 'IM_AJAX_CALL': 'Y', 'sessid': BX.bitrix_sessid()}
	});



};

MobileWebrtc.prototype.onCallback = function ()
{
	if (this.callBackUserId > 0)
		this.callInvite(this.callBackUserId);
};


MobileWebrtc.prototype.onClose = function ()
{
	this.callBackUserId = 0;
	this.resetState();
};

MobileWebrtc.prototype.callInvite = function (userId, video)
{
	if (userId == this.userId || this.callInit)
		return;
	var callVideo = ((typeof video == "undefined") ? "Y" : video);
	this.callInit = true;
	BX.ajax({
		url: this.signalingLink + '?CALL_INVITE',
		method: 'POST',
		dataType: 'json',
		timeout: 30,
		data: {'IM_CALL': 'Y', 'COMMAND': 'invite', 'MOBILE': 'Y', 'CHAT_ID': userId, 'CHAT': 'N', 'VIDEO': (callVideo ? 'Y' : 'N'), 'IM_AJAX_CALL': 'Y', 'sessid': BX.bitrix_sessid()},
		onsuccess: BX.delegate(function (params)
		{
			this.initiator = true;
			this.callChatId = params.CHAT_ID;
			this.callToGroup = params.CALL_TO_GROUP;
			this.callUserId = userId;
			webrtc.UI.show(
				webrtc.UI.state.OUTGOING_CALL,
				{
					"data": params,
					"recipient": {
						"avatar": params["HR_PHOTO"][this.callUserId],
						"name": params["USERS"][this.callUserId]["name"]
					},
					"caller": {
						"avatar": params["HR_PHOTO"][this.userId],
						"name": params["USERS"][this.userId]["name"]
					}
				}
			);


		}, this),
		onfailure: BX.delegate(function ()
		{
			//TODO error handling
			this.resetState();
			this.callInit = false;
			this.finishDialog();
		}, this)
	});

};

MobileWebrtc.prototype.onDisconnect = function ()
{
	this.peerConnectionInited = false;
};

MobileWebrtc.prototype.onIceCandidateDiscovered = function (params)
{
	this.iceCandidatesToSend.push({type: 'candidate', label: params.candidate.sdpMLineIndex, id: params.candidate.sdpMid, candidate: params.candidate.candidate});

	clearTimeout(this.iceCandidateTimeout);
	this.iceCandidateTimeout = setTimeout(BX.delegate(function ()
	{
		if (this.iceCandidatesToSend.length === 0)
			return false;

		this.onIceCandidate(this.callUserId, {'type': 'candidate', 'candidates': this.iceCandidatesToSend});
		this.iceCandidatesToSend = [];
	}, this), 250);
};

MobileWebrtc.prototype.onPeerConnectionCreated = function ()
{
	this.peerConnectionInited = true;
	if (this.initiator)
	{
		webrtc.createOffer();
	}
	else
	{
		webrtc.createAnswer({
			"sdp": this.remoteSessionDescription
		});
	}
};

MobileWebrtc.prototype.onIceCandidate = function (userID, candidates)
{
	this.callSignaling(userID, candidates);
};

MobileWebrtc.prototype.onIceConnectionStateChanged = function (params)
{
	//TODO to do something
};

MobileWebrtc.prototype.onIceGatheringStateChanged = function (params)
{
	//TODO to do something
};

MobileWebrtc.prototype.onSignalingStateChanged = function (params)
{
	//TODO to do something
};

MobileWebrtc.prototype.onLocalSessionDescriptionCreated = function (params)
{
	this.sessionDescription = params;
	if (this.iceCandidates.length > 0)
	{
		webrtc.addIceCandidates(this.iceCandidates);
		this.iceCandidates = [];
	}

	this.callSignaling(this.callUserId, this.sessionDescription);
};

MobileWebrtc.prototype.onUserMediaSuccess = function (params)
{
	webrtc.UI.showLocalVideo();
	this.connected[this.userId] = true;
	this.callCommand(this.callChatId, "ready");
	if (this.connected[this.callUserId] && this.initiator)
	{
		webrtc.createPeerConnection();
	}
};

MobileWebrtc.prototype.onError = function (errorData)
{
	//TODO handle error
	this.resetState();
};

MobileWebrtc.prototype.resetState = function ()
{
	this.connected = {};
	this.initiator = false;
	this.callInit = false;
	this.callActive = false;
	this.callChatId = 0;
	this.callUserId = 0;
	this.isMobile = false;
	this.peerConnectionInited = false;
	this.iceCandidates = [];
	this.iceCandidatesToSend = [];
};

MobileWebrtc.prototype.callSignaling = function (userID, params)
{
	BX.ajax(
	{
		url: this.signalingLink + '?CALL_SIGNALING',
		method: 'POST',
		dataType: 'json',
		timeout: 30,
		data: {
			'IM_CALL': 'Y',
			'COMMAND': 'signaling',
			'CHAT_ID': this.callChatId,
			'RECIPIENT_ID': userID,
			'PEER': JSON.stringify(params),
			'IM_AJAX_CALL': 'Y',
			'sessid': BX.bitrix_sessid()
		}
	});
};

MobileWebrtc.prototype.callCommand = function (chatId, command, params, async)
{
	chatId = parseInt(chatId);
	async = async != false;
	params = typeof(params) == 'object' ? params : {};

	if (chatId > 0)
	{
		BX.ajax({
			url: this.signalingLink + '?CALL_SHARED',
			method: 'POST',
			dataType: 'json',
			timeout: 30,
			async: async,
			data: {'IM_CALL': 'Y', 'COMMAND': command, 'CHAT_ID': chatId, 'RECIPIENT_ID': this.callUserId, 'PARAMS': JSON.stringify(params), 'IM_AJAX_CALL': 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function ()
			{
//				webrtc.UI.show(
//					webrtc.UI.state.FAIL_CALL
//
			}, this)
		});
	}
};


MobileWebrtc.prototype.finishDialog = function ()
{
	webrtc.UI.close();
};

MobileWebrtc.prototype.signalingPeerData = function (userId, peerData)
{
	var signal = JSON.parse(peerData);

	if (signal.type === 'offer')
	{
		this.remoteSessionDescription = signal["sdp"];
		webrtc.createPeerConnection();
	}
	else if (signal.type === 'answer')
	{
		webrtc.setRemoteDescription(signal);
	}
	else if (signal.type === 'candidate')
	{
		if (this.peerConnectionInited)
		{
			webrtc.addIceCandidates(signal.candidates);
		}
		else
		{
			for (var i = 0; i < signal.candidates.length; i++)
				this.iceCandidates.push(signal.candidates[i]);
		}
	}
}

window.mwebrtc = new MobileWebrtc();

BX.addCustomEvent("onPullEvent-im", BX.proxy(function (command, params)
{
	if (command == 'call')
	{
		if (params.command == 'ready')
		{
			this.connected[this.callUserId] = true;

			if (this.connected[this.userId] && this.initiator == true)
			{
				webrtc.createPeerConnection();
			}
		}
		else if (params.command == 'decline' && this.callInit)
		{
			if (this.callChatId == params.chatId)
			{
				if (this.initiator && !this.connected[this.callUserId])
				{
					this.callBackUserId = this.callUserId;
					webrtc.UI.show(
						webrtc.UI.state.FAIL_CALL,
						{
							'message': BX.message("MOBILEAPP_CALL_DECLINE")
						}
					);
				}
				else
				{
					webrtc.UI.close();
				}

				this.resetState()

			}

		}
		else if (params.command == 'waitTimeout')
		{
			if (this.callChatId == params.chatId)
			{
				this.resetState();
				this.finishDialog();
			}

		}
		else if (params.command == 'invite' || params.command == 'invite_join')
		{
			if (!this.callInit)
			{
				this.callChatId = params.chatId;
				this.callToGroup = false;
				this.callUserId = params.senderId;
				this.initiator = false;
				this.callInit = true;
				this.callCommand(this.callChatId, 'wait');
				webrtc.UI.show(
					webrtc.UI.state.INCOMING_CALL,
					{
						"data": params,
						"caller": {
							"name": params["users"][params.senderId]["name"],
							"avatar": params["hrphoto"][params.senderId]
						}
					}
				);
			}
			else if (params.command == 'invite')
			{
				if (this.callChatId == params.chatId)
				{
					this.callCommand(params.chatId, 'busy_self');
				}
				else
				{
					setTimeout(BX.delegate(function ()
					{
						BX.ajax({
							url: this.signalingLink + '?CALL_BUSY',
							method: 'POST',
							dataType: 'json',
							timeout: 30,
							data: {'IM_CALL': 'Y', 'COMMAND': 'busy', 'CHAT_ID': params.chatId, 'RECIPIENT_ID': params.senderId, 'VIDEO': params.video ? 'Y' : 'N', 'IM_AJAX_CALL': 'Y', 'sessid': BX.bitrix_sessid()}
						});
					}, this), params.callToGroup ? 1000 : 0);
				}
			}
			else if (this.callChatId == params.chatId)
			{
				this.onAnswer(params);
			}
		}
		else if (params.command == 'answer' && this.initiator == true)
		{
			if (this.callInit)
			{
				this.initiator = true;
				this.callActive = true;
				webrtc.UI.show(
					webrtc.UI.state.CONVERSATION
				);
				webrtc.getUserMedia();
			}

		}
		else if (params.command == 'decline_self' && this.callChatId == params.chatId || params.command == 'answer_self' && !this.callActive)
		{
			this.resetState();
			this.finishDialog();
		}
		else if(this.callInit && this.callChatId == params.chatId)
		{
			if (params.command == 'signaling' && this.connected[this.userId])
			{
				if (this.callInit && this.callChatId == params.chatId)
					this.signalingPeerData(params.senderId, params.peer);
			}
			else if (params.command == 'busy')
			{

				if (this.callInit && this.callChatId == params.chatId)
				{
					this.callBackUserId = this.callUserId;
					webrtc.UI.show(
						webrtc.UI.state.FAIL_CALL,
						{
							'message': BX.message("MOBILEAPP_CALL_BUSY")
						}
					);
					this.resetState();
				}
			}
			else if (params.command == 'errorAccess')
			{

				if (this.callInit && this.callChatId == params.chatId)
				{
					this.callBackUserId = this.callUserId;
					webrtc.UI.show(
						webrtc.UI.state.FAIL_CALL,
						{
							'message': BX.message('MOBILEAPP_CALL_NO_ACCESS')
						}
					);
					this.callInit = false;
				}
			}
			else if (params.command == 'reconnect')
			{
				this.initiator = false;
				webrtc.onReconnect();
			}
		}

	}
}, mwebrtc));

