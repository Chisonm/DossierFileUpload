import {
  useQuery,
  useMutation,
  useQueryClient,
  keepPreviousData,
} from "@tanstack/react-query";
import type { DossierFilesResponse, FileType } from "../types/file";
import { uploadFile, getDossierFiles, deleteFile } from "../api/fileApi";
import { QUERY_KEYS } from "~/constants/filekeys";
import { toast } from "sonner";
import type { AxiosError } from "axios";

export const useDossierFiles = () => {
  return useQuery({
    queryKey: [QUERY_KEYS.files.GET_FILES],
    queryFn: () => getDossierFiles(),
    select: (data: DossierFilesResponse) => data.data,
    placeholderData: keepPreviousData,
  });
};

export const useUploadFile = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ file, fileType }: { file: File; fileType: FileType }) =>
      uploadFile(file, fileType),
    onSuccess: () => {
      toast.success("File uploaded successfully");
      queryClient.invalidateQueries({
        queryKey: [QUERY_KEYS.files.GET_FILES],
      });
    },
    onError: (error: AxiosError) => {
      console.group(error);
      toast.error(error.response?.data?.message);
    },
  });
};

export const useDeleteFile = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: deleteFile,
    onSuccess: (_, fileId) => {
      toast.success("File deleted successfully");
      queryClient.invalidateQueries({ queryKey: [QUERY_KEYS.files.GET_FILES] });
    },
    onError: (error: Error) => {
      toast.error(`Failed to delete file: ${error.message}`);
    },
  });
};
