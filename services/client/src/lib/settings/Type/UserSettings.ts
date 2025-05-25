export type UserSettings = {
  allowRegistration: boolean;
  allowUnauthenticatedAccess: boolean;
  approvalRequired: boolean;
  password: {
    minLength: number | undefined;
    requirements: number;
  };
};
