export class ApiError extends Error {
  public readonly status: number;
  public readonly code?: string;
  public readonly details?: unknown;

  constructor(
    message: string,
    status: number,
    code?: string,
    details?: unknown,
  ) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.code = code;
    this.details = details;
  }

  public static fromResponse(response: Response, details?: unknown): ApiError {
    return new ApiError(
      `API request failed: ${response.statusText}`,
      response.status,
      response.status.toString(),
      details,
    );
  }

  public isUnauthorized(): boolean {
    return this.status === 401;
  }

  public isForbidden(): boolean {
    return this.status === 403;
  }

  public isServerError(): boolean {
    return this.status >= 500;
  }

  public isClientError(): boolean {
    return this.status >= 400 && this.status < 500;
  }
}

export class TokenRefreshError extends ApiError {
  constructor(message: string = 'Token refresh failed', details?: unknown) {
    super(message, 401, 'TOKEN_REFRESH_FAILED', details);
    this.name = 'TokenRefreshError';
  }
}

export const handleApiError = (error: unknown): ApiError => {
  if (error instanceof ApiError) {
    return error;
  }

  if (error instanceof Error) {
    return new ApiError(error.message, 500, 'UNKNOWN_ERROR', error);
  }

  return new ApiError('Unknown error occurred', 500, 'UNKNOWN_ERROR', error);
};
