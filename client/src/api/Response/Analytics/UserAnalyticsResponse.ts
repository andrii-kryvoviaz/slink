export type UserAnalyticsData = {
  active: number;
  deleted: number;
  suspended: number;
};

export type UserAnalyticsResponse = {
  data: UserAnalyticsData;
};
