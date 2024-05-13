export const isJson = (str: string): boolean => {
  try {
    JSON.parse(str);
    return true;
  } catch (e) {
    return false;
  }
};

export const tryJson = <T>(str: any): T | string => {
  try {
    return JSON.parse(str);
  } catch (e) {
    return str;
  }
};
