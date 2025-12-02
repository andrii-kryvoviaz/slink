import { AbstractResource } from '@slink/api/AbstractResource';
import type {
  NotificationListingResponse,
  UnreadCountResponse,
} from '@slink/api/Response';

export class NotificationResource extends AbstractResource {
  public async getNotifications(
    page: number = 1,
    limit: number = 20,
  ): Promise<NotificationListingResponse> {
    const searchParams = new URLSearchParams({
      page: page.toString(),
      limit: limit.toString(),
    });

    return this.get(`/notifications?${searchParams.toString()}`);
  }

  public async getUnreadCount(): Promise<UnreadCountResponse> {
    return this.get('/notifications/unread-count');
  }

  public async markAsRead(notificationId: string): Promise<void> {
    return this.post(`/notifications/${notificationId}/read`);
  }

  public async markAllAsRead(): Promise<void> {
    return this.post('/notifications/mark-all-read');
  }
}
