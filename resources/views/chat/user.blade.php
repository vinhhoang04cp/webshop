@include('chat.shared', [
	'mode' => 'user',
	'currentUser' => $currentUser,
	'chatUser' => $chatUser,
	'chatUserId' => $chatUserId,
	'apiToken' => $apiToken,
	'pusher' => $pusher
])


