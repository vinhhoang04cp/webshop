import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

// Không tạo Echo instance ở đây - sẽ được tạo trong ChatManager với config từ server
// Chỉ export để sử dụng trong chat.js
export { Echo, Pusher };
