export type ViolationResponse = {
  title: string;
  message: string;
  violations: Violation[];
};

export type Violation = {
  property: string;
  message: string;
};
