export function getFileType(mimeType: string): string {
  if (!mimeType) return "Unknown";

  const mime = mimeType.toLowerCase();

  if (mime.startsWith("image/")) return "Image";
  if (mime.includes("pdf")) return "PDF";

  return "File";
}
