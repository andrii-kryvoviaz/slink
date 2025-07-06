export type UserSettings = {
  allowRegistration: boolean;
  approvalRequired: boolean;
  password: {
    minLength: number | undefined;
    requirements: number;
  };
};
