export const HttpStatus = {
  NoContent: 204,
  BadRequest: 400,
  Unauthorized: 401,
  Forbidden: 403,
  NotFound: 404,
  Gone: 410,
  PayloadTooLarge: 413,
  UnprocessableEntity: 422,
  Locked: 423,
  InternalServerError: 500,
} as const;
