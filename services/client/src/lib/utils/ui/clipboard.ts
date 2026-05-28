import { sounds } from './feedback';

export const playCopyFeedback = (): void => {
  sounds.thock();
};

export const copyText = async (text: string): Promise<boolean> => {
  try {
    await navigator.clipboard.writeText(text);
    playCopyFeedback();
    return true;
  } catch (error) {
    console.error('Failed to copy text:', error);
    return false;
  }
};

export const copyClipboardItems = async (
  items: ClipboardItem[],
): Promise<boolean> => {
  try {
    await navigator.clipboard.write(items);
    playCopyFeedback();
    return true;
  } catch (error) {
    console.error('Failed to copy clipboard items:', error);
    return false;
  }
};
