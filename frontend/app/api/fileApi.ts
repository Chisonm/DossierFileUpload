import type {
  DossierFilesResponse,
  FileUploadResponse,
  FileType,
  ApiResponse,
} from "../types/file";
import api from "./api";

export const uploadFile = async (
  file: File,
  fileType: FileType
): Promise<FileUploadResponse> => {
  const formData = new FormData();
  formData.append("file", file);
  formData.append("file_type", fileType);

  const response = await api.post<ApiResponse<FileUploadResponse>>(
    `/api/dossier-files`,
    formData,
    {
      headers: {
        Accept: "application/json",
        "Content-Type": "multipart/form-data",
      },
    }
  );

  return response.data.data;
};

export const getDossierFiles = async (): Promise<DossierFilesResponse> => {
  const response = await api.get<DossierFilesResponse>(
    `/api/dossier-files`
  );
  return response.data;
};

export const deleteFile = async (fileId: string): Promise<void> => {
  await api.delete(`/api/dossier-files/${fileId}`);
};
