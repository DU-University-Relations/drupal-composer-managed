import path from 'path';

export const getAssetPath = (relativePath: string) => {
  return path.join(__dirname, '..', 'assets', relativePath);
};