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
    return this.get('/notifications', {
      query: { page, limit },
    });
  }

  public async exists(): Promise<boolean> {
    const response = await this.get<{ exists: boolean }>(
      '/notifications/exists',
    );
    return response.exists;
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
