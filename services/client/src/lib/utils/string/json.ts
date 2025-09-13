export const isJson = (str: string): boolean => {
  try {
    JSON.parse(str);
    return true;
  } catch {
    return false;
  }
};

export const tryJson = <T>(str: string): T | string => {
  try {
    return JSON.parse(str);
  } catch {
    return str;
  }
};
