import type { User } from '@slink/lib/auth/Type/User';

export interface ApiKeyFormData {
  name: string;
  expiresAt?: Date | null;
}

export interface ApiKeyManagerProps {
  user: User;
}

export interface ApiKeyManagerState {
  createModalOpen: boolean;
  createdKeyModalOpen: boolean;
  formData: ApiKeyFormData;
  errors: Record<string, string>;
}
