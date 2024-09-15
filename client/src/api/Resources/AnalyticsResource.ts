import { AbstractResource } from '@slink/api/AbstractResource';
import type {
  ImageAnalyticsResponse,
  UserAnalyticsResponse,
} from '@slink/api/Response';

export class AnalyticsResource extends AbstractResource {
  public async getUserAnalytics(): Promise<UserAnalyticsResponse> {
    return this.get('/analytics/user');
  }

  public async getImageAnalytics({
    dateInterval,
  }: { dateInterval?: string } = {}): Promise<ImageAnalyticsResponse> {
    const query = dateInterval ? { dateInterval } : {};

    return this.get(`/analytics/image`, { query });
  }
}
