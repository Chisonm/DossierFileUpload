import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import type { DossierFilesResponse, UploadedFile, FileType } from '../types/file';
import type { FileUploadResponse } from '../types/file';
import { uploadFile, getDossierFiles, deleteFile } from '../api/fileApi';
import { FILES_KEY } from '~/constants/filekeys';

export const useDossierFiles = () => {
  return useQuery({
    queryKey: FILES_KEY,
    queryFn: async () => getDossierFiles(),
    select: (data: DossierFilesResponse) => data,
  });
};

export const useUploadFile = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ file, fileType }: { file: File; fileType: FileType }) => 
      uploadFile(file, fileType),
    onSuccess: (data: FileUploadResponse) => {
      queryClient.invalidateQueries({ queryKey: FILES_KEY });
    },
  });
};

export const useDeleteFile = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: deleteFile,
    onSuccess: (_, fileId) => {
      queryClient.invalidateQueries({ queryKey: FILES_KEY });
    },
  });
};
