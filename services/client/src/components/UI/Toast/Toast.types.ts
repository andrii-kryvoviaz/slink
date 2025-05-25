export type ToastType = 'success' | 'warning' | 'error' | 'info' | 'component';

export type ToastIcon = {
  icon: string;
  iconColor: string;
};

export type ToastOptions = Partial<ToastIcon> &
  (
    | {
        type: Exclude<ToastType, 'component'>;
        message: string;
        component?: never;
        props?: never;
      }
    | { type: 'component'; component: any; props?: any; message?: never }
  ) & {
    id?: string;
    duration?: number;
  };
