<template>
    <div>
        <notification
            v-for="(notification, index) in notificationsList"
            :key="index" 
            :notification="notification"
            @click="markAsRead"
            class="m-top-xs"
        >
        </notification>
        <button class="button pointer" @click="markAllAsRead" v-if="!isAllRead">Mark all as read</button>
    </div>
</template>

<script>
import Notification from './Notification.vue';
import axios from 'axios';

export default {
    props: ['notifications'],
    components: { Notification },
    data() {
        return {
            notificationsList: this.notifications
        }
    },
    computed: {
        isAllRead() {
            return this.notificationsList.findIndex(notification => notification.read_at === null) == -1;
        }
    },
    methods: {
        async markAsRead(notification) {
            if (! notification.isRead) {
                await axios.patch(`/notifications/${notification.id}`);
                const index = this.notificationsList.findIndex(notificationInList => notification.id === notificationInList.id);
                if (index > -1) {
                    this.notificationsList[index].read_at = new Date();
                }
            }
            
            window.location.href = notification.data.link;
        },
        markAllAsRead() {
            axios.patch("/notifications/read")
                .then(() => {
                    this.notificationsList.map(notification => notification.read_at = new Date())
                })
        }
    }
}
</script>

