export interface UploadedFile {
  id: string;
  filename: string;
  originalName: string;
  mimetype: string;
  size: number;
  url: string;
  uploadedAt: string;
}

export interface FileUploadResponse {
  file: UploadedFile;
  message: string;
}

export interface DossierFiles {
  passport: UploadedFile[];
  utility_bill: UploadedFile[];
  other: UploadedFile[];
}

export interface DossierFilesResponse {
  data: DossierFiles;
  message: string;
}

export type FileType = 'passport' | 'utility_bill' | 'other';