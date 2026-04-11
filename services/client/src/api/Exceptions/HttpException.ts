import type { NumericRange } from '@sveltejs/kit';

import { t } from '@slink/lib/utils/i18n';

export type ErrorList = { [name: string]: string | ErrorList };
export abstract class HttpException extends Error {
  protected constructor(
    message: string,
    public status: NumericRange<400, 451>,
  ) {
    super(t(message));
  }
  get error() {
    return this.message;
  }

  get errors(): ErrorList {
    return { message: this.message };
  }
}
