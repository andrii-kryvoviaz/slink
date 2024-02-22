export type ErrorList = { name: string; message: string }[];
export abstract class HttpException extends Error {
  constructor(message: string, public status: number) {
    super(message);
  }
  get error() {
    return this.message;
  }

  get errors(): ErrorList {
    return [
      {
        name: 'error',
        message: this.message,
      },
    ];
  }
}
