export type UserSettings = {
  allowUnauthenticatedAccess: boolean;
  approvalRequired: boolean;
  password: {
    minLength: number | undefined;
    requirements: number;
  };
};
