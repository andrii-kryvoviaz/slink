export type StorageProvider = 'local' | 'smb' | 's3';

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
    s3: {
      region: string;
      bucket: string;
      key: string;
      secret: string;
    };
  };
};
