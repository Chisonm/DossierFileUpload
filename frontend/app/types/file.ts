export interface ApiResponse<T> {
  data: T;
  message?: string;
}

export interface UploadedFile {
  id: string;
  filename: string;
  original_filename: string;
  mime_type: string;
  size: number;
  file_url: string;
  human_size: string;
  file_path: string;
  created_at: string;
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

export type FileType = "passport" | "utility_bill" | "other";
