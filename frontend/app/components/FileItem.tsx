import { TrashIcon, EyeIcon, DocumentIcon } from "@heroicons/react/24/outline";

import { useDeleteFile } from "~/hooks/useFiles";
import type { UploadedFile } from "~/types/file";
import { getFileType } from "~/utils/functions";

interface FileItemProps {
  file: UploadedFile;
}
export const BASE_API = import.meta.env.VITE_BASE_URL;
const FileItem = ({ file }: FileItemProps) => {
  const deleteFileMutation = useDeleteFile();

  const handleDeleteFile = (fileId: string) => {
    deleteFileMutation.mutate(fileId);
  };

  const openFile = (file: UploadedFile) => {
    if (!file.file_url) {
      return;
    }

    const filePathURL = `${BASE_API}${file.file_url}`;
    window.open(filePathURL, "_blank");
  };

  return (
    <div className="border border-gray-200 rounded-lg p-4  bg-gray-50">
        {/* {file.file_url} */}
      <div className="flex justify-between items-center gap-2">
        <div className="flex items-center gap-x-1">
          <DocumentIcon className="size-8 text-gray-400" />
          <div>
            <h3 className="font-medium text-gray-900 truncate w-[180px] text-sm">
              {file.original_filename || file.filename}
            </h3>
            <p className="text-sm text-gray-500">
              {file.mime_type ? getFileType(file.mime_type) : ""}
              {" - "}
              {file.human_size}
            </p>
            {file.created_at ? (
              <p className="text-xs text-gray-400">
                {new Date(file.created_at).toLocaleString()}
              </p>
            ) : null}
          </div>
        </div>
        <div className="flex space-x-2">
          <button
            onClick={() => openFile(file)}
            className="text-primary-600 hover:text-primary-800 transition-colors cursor-pointer"
            title="Preview file"
          >
            <EyeIcon className="h-5 w-5" />
          </button>
          <button
            onClick={() => handleDeleteFile(file.id)}
            disabled={deleteFileMutation.isPending}
            className={`transition-colors ${
              deleteFileMutation.isPending
                ? "text-gray-400 cursor-not-allowed"
                : "text-red-500 hover:text-red-700"
            } cursor-pointer`}
            title={deleteFileMutation.isPending ? "Deleting..." : "Delete file"}
          >
            {deleteFileMutation.isPending ? (
              <div className="h-5 w-5 animate-spin rounded-full border-2 border-red-500 border-t-transparent" />
            ) : (
              <TrashIcon className="h-5 w-5" />
            )}
          </button>
        </div>
      </div>
    </div>
  );
};

export default FileItem;
