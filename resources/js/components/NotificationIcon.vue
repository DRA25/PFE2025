<script setup>
import { ref, onMounted } from 'vue';
import { BellIcon } from '@heroicons/vue/24/outline';
import { router } from '@inertiajs/vue3';
import axios from 'axios'; // Ensure axios is imported if not globally available

const showNotifications = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);

const toggleNotifications = () => {
    showNotifications.value = !showNotifications.value;
    if (showNotifications.value) {
        fetchNotifications();
    }
};

const fetchNotifications = async () => {
    try {
        const response = await axios.get('/notifications');
        notifications.value = response.data.notifications;
        unreadCount.value = response.data.unreadCount;
    } catch (error) {
        console.error('Error fetching notifications:', error);
    }
};

const markAsRead = async (notification) => {
    try {
        // Simply mark the notification as read
        await axios.post(`/notifications/${notification.id}/read`);

        // Refresh the notifications list
        fetchNotifications();
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
};

const markAllAsRead = async () => {
    try {
        await axios.post('/notifications/mark-all-read');
        fetchNotifications(); // Refresh after marking all as read
    } catch (error) {
        console.error('Error marking all notifications as read:', error);
    }
};

const formatTime = (dateString) => {
    const date = new Date(dateString);
    // You can customize the date format further if needed, e.g., 'en-US', { hour: '2-digit', minute: '2-digit' }
    return date.toLocaleString();
};

onMounted(() => {
    fetchNotifications();

    // Refresh notifications every 30 seconds
    // Consider using a WebSocket/Pusher for real-time updates in a production environment
    setInterval(fetchNotifications, 30000);
});
</script>

<template>
    <div class="relative font-sans">
        <!-- Notification Bell Button -->
        <button @click="toggleNotifications"
                class="relative p-2 rounded-full text-gray-600 hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200
                       dark:text-gray-300 dark:hover:text-white dark:focus:ring-offset-gray-900">
            <span class="sr-only">View notifications</span>
            <BellIcon class="h-6 w-6" aria-hidden="true" />
            <span v-if="unreadCount > 0"
                  class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-500 rounded-full group-hover:scale-105 transition-transform duration-200">
                {{ unreadCount }}
            </span>
        </button>

        <!-- Notification Dropdown Box -->
        <div v-if="showNotifications"
             class="origin-top-right absolute right-0 mt-3 w-80 rounded-xl shadow-2xl bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50 transform transition-all duration-300 ease-out scale-95 opacity-0
                    dark:bg-gray-800 dark:ring-gray-700 dark:shadow-3xl"
             :class="{ 'scale-100 opacity-100': showNotifications }">

            <!-- Dropdown Header -->
            <div class="px-5 py-3 border-b border-gray-200 bg-gray-50 rounded-t-xl
                        dark:border-gray-700 dark:bg-gray-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Notifications</h3>
            </div>

            <!-- Notification List Body -->
            <div v-if="notifications.length > 0" class="max-h-96 overflow-y-auto custom-scrollbar">
                <div v-for="notification in notifications" :key="notification.id"
                     @click="markAsRead(notification)"
                     class="flex flex-col px-5 py-3 hover:bg-indigo-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors duration-150
                            dark:border-gray-700 dark:hover:bg-indigo-700"
                     :class="{ 'bg-blue-50 dark:bg-blue-900': notification.read_at === null, 'dark:text-gray-200': notification.read_at !== null }">
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white leading-snug">{{ notification.data.message }}</p>
                        <span v-if="notification.read_at === null" class="h-2.5 w-2.5 rounded-full bg-indigo-500 flex-shrink-0 ml-2 animate-pulse"></span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        {{ notification.data.piece_name }} ({{ notification.data.quantity }})
                    </p>
                    <p class="text-xs text-gray-400 mt-1 dark:text-gray-400">
                        {{ formatTime(notification.created_at) }}
                    </p>
                </div>
            </div>
            <div v-else class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                No new notifications.
            </div>

            <!-- Dropdown Footer -->
            <div class="px-5 py-3 border-t border-gray-200 bg-gray-50 rounded-b-xl
                        dark:border-gray-700 dark:bg-gray-700">
                <button @click="markAllAsRead"
                        class="w-full text-center text-sm font-medium text-indigo-600 hover:text-indigo-800 hover:underline focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200
                               dark:text-indigo-400 dark:hover:text-indigo-300 dark:focus:ring-offset-gray-700">
                    Mark all as read
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar for better aesthetics */
.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Dark mode scrollbar styles */
html.dark .custom-scrollbar::-webkit-scrollbar-track {
    background: #333;
}

html.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #555;
}

html.dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #777;
}
</style>
