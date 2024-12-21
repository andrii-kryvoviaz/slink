export type StorageProvider = 'local' | 'smb';

export type StorageSettings = {
  provider: StorageProvider;
  adapter: {
    local: {
      dir: string;
    };
    smb: {
      host: string;
      share: string;
      workgroup: string;
      username: string;
      password: string;
    };
  };
};
