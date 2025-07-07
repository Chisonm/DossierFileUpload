export function getFileType(mimeType: string): string {
  if (!mimeType) return "Unknown";

  const mime = mimeType.toLowerCase();

  if (mime.startsWith("image/")) return "Image";
  if (mime.startsWith("video/")) return "Video";
  if (mime.startsWith("audio/")) return "Audio";
  if (mime.startsWith("text/")) return "Text";
  if (mime.includes("pdf")) return "PDF";
  if (mime.includes("word") || mime.includes("document")) return "Document";
  if (mime.includes("excel") || mime.includes("spreadsheet"))
    return "Spreadsheet";
  if (mime.includes("powerpoint") || mime.includes("presentation"))
    return "Presentation";
  if (mime.includes("zip") || mime.includes("rar") || mime.includes("archive"))
    return "Archive";
  if (mime.includes("json")) return "JSON";
  if (mime.includes("xml")) return "XML";

  return "File";
}
