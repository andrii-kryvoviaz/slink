export type UserSettings = {
  allowUnauthenticatedAccess: boolean;
  approvalRequired: boolean;
  password: {
    minLength: number;
    requirements: number;
  };
};
