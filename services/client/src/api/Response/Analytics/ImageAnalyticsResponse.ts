type ImageAnalyticsData = {
  date: string;
  count: number;
};

export type ImageAnalyticsResponse = {
  data: ImageAnalyticsData[];
  availableIntervals: { [key: string]: string };
};
