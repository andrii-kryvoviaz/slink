export type UserSettings = {
  allowRegistration: boolean;
  localAuthEnabled: boolean;
  approvalRequired: boolean;
  password: {
    minLength: number | undefined;
    requirements: number;
  };
  autoRedirectProviderId: string | null;
};
