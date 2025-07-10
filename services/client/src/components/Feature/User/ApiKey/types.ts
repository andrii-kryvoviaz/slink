export interface ApiKeyFormData {
  name: string;
  expiresAt?: string;
}

export interface ApiKeyManagerProps {
  user: any;
}

export interface ApiKeyManagerState {
  createModalOpen: boolean;
  createdKeyModalOpen: boolean;
  formData: ApiKeyFormData;
  errors: Record<string, string>;
}
