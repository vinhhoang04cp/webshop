@include('chat.shared', [
	'mode' => 'admin',
	'currentUser' => $currentUser,
	'chatUser' => $chatUser,
	'chatUserId' => $chatUserId,
	'apiToken' => $apiToken,
	'pusher' => $pusher
])


