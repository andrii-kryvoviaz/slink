export const HttpStatus = {
  NoContent: 204,
  BadRequest: 400,
  Unauthorized: 401,
  Forbidden: 403,
  NotFound: 404,
  PayloadTooLarge: 413,
  UnprocessableEntity: 422,
  Locked: 423,
} as const;
