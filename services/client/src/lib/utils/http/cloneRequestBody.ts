export const cloneRequestBody = async (
  request: Request,
): Promise<globalThis.BodyInit | undefined> => {
  const contentType = request.headers.get('content-type');

  if (contentType && /^(application\/json.*)/.test(contentType)) {
    return await request.clone().arrayBuffer();
  }

  if (contentType && /^(multipart\/form-data.*)/.test(contentType)) {
    return request.clone().body as globalThis.BodyInit;
  }

  return;
};
