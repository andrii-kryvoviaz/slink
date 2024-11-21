import type { NumericRange } from '@sveltejs/kit';

export type ErrorList = { [name: string]: string | ErrorList };
export abstract class HttpException extends Error {
  protected constructor(
    message: string,
    public status: NumericRange<400, 451>
  ) {
    super(message);
  }
  get error() {
    return this.message;
  }

  get errors(): ErrorList {
    return { error: this.message };
  }
}
