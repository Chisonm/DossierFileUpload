import { HTTP_OK, HTTP_REQUEST_ENTITY_TOO_LARGE } from "~/constants/statusCode";
import type {
  DossierFilesResponse,
  FileUploadResponse,
  FileType,
} from "../types/file";
import api from "./api";

export interface ApiResponse<T> {
  data: T;
  message?: string;
}

export const uploadFile = async (
  file: File,
  fileType: FileType
): Promise<FileUploadResponse> => {
  try {
    const formData = new FormData();
    formData.append("file", file);
    formData.append("file_type", fileType);

    const response = await api.post<ApiResponse<FileUploadResponse>>("/dossier-files", formData,
      {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      }
    );

    return response.data.data;
  } catch (error) {
    throw error;
  }
};

export const getDossierFiles = async (): Promise<DossierFilesResponse> => {
  try {
    const response = await api.get<ApiResponse<DossierFilesResponse>>(
      "/dossier-files"
    );

    return response.data.data;
  } catch (error) {
    throw error;
  }
};

export const deleteFile = async (fileId: string): Promise<void> => {
  try {
       await api.delete<ApiResponse<void>>(`/dossier-files/${fileId}`,
      {
        method: "DELETE",
      }
    );
    return;
  } catch (error) {
    console.error("Error deleting file:", error);
    throw error;
  }
};
