<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';
import axios from 'axios';

const messages = ref([]);
const newMessage = ref('');
const photo = ref(null);
const photoPreview = ref(null);
const typingUser = ref(null);
let typingTimeout = null;

const props = defineProps(['chatUser']);
const { chatUser } = props;

const authUserId = window?.Laravel?.user?.id ?? null; // assuming you pass logged-in user id in blade

const scrollToBottom = async () => {
    await nextTick();
    const messageList = document.getElementById('messagelist');
    if (messageList) {
        messageList.scrollTo({
            top: messageList.scrollHeight,
            behavior: 'smooth'
        });
    }
};

const getMessages = async () => {
    try {
        const response = await axios.get('/messages?chat_user_id=' + chatUser.id);
        messages.value = response.data;
        await scrollToBottom();
    } catch (err) {
        console.error(err.message);
    }
};

const postMessage = async () => {
    try {
        const formData = new FormData();
        formData.append('text', newMessage.value);
        formData.append('user_id', chatUser.id);
        if (photo.value) {
            formData.append('photo', photo.value);
        }

        const response = await axios.post('/message', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        messages.value.push(response.data);
        newMessage.value = '';
        photo.value = null;
        photoPreview.value = null;
        await scrollToBottom();
    } catch (err) {
        console.error(err.message);
    }
};

const sendMessage = () => {
    if (newMessage.value.trim() || photo.value) {
        postMessage();
    }
};

const handlePhotoUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        photo.value = file;
        photoPreview.value = URL.createObjectURL(file);
    }
};

const sendTypingEvent = () => {
    window.Echo.private("channel_for_everyone")
        .whisper('typing', {
            user_id: authUserId,
            name: window?.Laravel?.user?.name ?? 'Someone'
        });
};

// Watch message input for typing
watch(newMessage, (val) => {
    if (val.trim() !== "") {
        sendTypingEvent();
    }
});

// Echo listeners
const setupEchoListener =  () => {
    window.Echo.private("channel_for_everyone")
        .listen('GotMessage', async () => {
            await getMessages();
        })
        .listenForWhisper('typing', (e) => {
            console.log(`${e.name} is typing...`);
            // Only show typing if it's NOT the current user
           scrollToBottom();
                typingUser.value = e.name;
                console.log(`${e.name} is typing...`);
                if (typingTimeout) clearTimeout(typingTimeout);
                typingTimeout = setTimeout(() => {
                    typingUser.value = null;
                    console.log('Typing stopped');
                }, 3000);
            
        });
};

onMounted(() => {
    getMessages();
    setupEchoListener();
});
</script>

<template>
  <div class="chat-container">
    <div class="chat-header">
      Chat with {{ chatUser.name }}
    </div>

    <div class="chat-box" id="messagelist">
      <div
        v-for="(message, index) in messages"
        :key="index"
        class="message"
        :class="{ 'sent': message.user_id === authUserId, 'received': message.user_id !== authUserId }"
      >
        <div class="message-content">
          <strong>{{ message.user?.name }}:</strong>
          <p class="text-muted">{{ message.text }}</p>
          <img v-if="message.photo_url" :src="message.photo_url" class="message-photo" />
          <small>{{ message.time }}</small>
        </div>
      </div>

      <!-- Typing indicator -->
      <div v-if="typingUser" class="typing-indicator">
        {{ typingUser }} is typing...
      </div>
    </div>

    <div class="input-area">
      <input type="file" @change="handlePhotoUpload" />
      <input
        v-model="newMessage"
        @keyup.enter="sendMessage"
        type="text"
        placeholder="Type your message..."
      />
      <button @click="sendMessage">Send</button>
    </div>

    <div v-if="photoPreview" class="photo-preview">
      <img :src="photoPreview" alt="Preview" />
    </div>
  </div>
</template>

<style scoped>
.chat-container {
  display: flex;
  flex-direction: column;
  height: 500px;
  border: 1px solid #ccc;
  border-radius: 10px;
  overflow: hidden;
}

.chat-header {
  background-color: #0078ff;
  color: white;
  padding: 10px;
  font-weight: bold;
}

.chat-box {
  flex: 1;
  overflow-y: auto;
  padding: 15px;
  background-color: #f5f5f5;
}

.message {
  display: flex;
  margin-bottom: 10px;
}

.message.sent {
  justify-content: flex-end;
}

.message.received {
  justify-content: flex-start;
}

.message-content {
  max-width: 60%;
  padding: 10px;
  border-radius: 10px;
  background-color: white;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.message.sent .message-content {
  background-color: #dcf8c6;
}

.message-photo {
  display: block;
  margin-top: 5px;
  max-width: 200px;
  border-radius: 8px;
}

.input-area {
  display: flex;
  padding: 10px;
  border-top: 1px solid #ddd;
  background-color: white;
}

.input-area input[type="text"] {
  flex: 1;
  margin: 0 5px;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 20px;
}

.input-area button {
  background-color: #0078ff;
  color: white;
  border: none;
  padding: 8px 12px;
  border-radius: 20px;
  cursor: pointer;
}

.photo-preview {
  padding: 10px;
  text-align: center;
  background-color: #fafafa;
}

.photo-preview img {
  max-width: 150px;
  border-radius: 8px;
}

.typing-indicator {
  font-style: italic;
  font-size: 0.9rem;
  color: gray;
  padding: 3px 0;
}
</style>
