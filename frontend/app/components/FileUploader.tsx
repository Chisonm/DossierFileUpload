import { ArrowUpTrayIcon } from "@heroicons/react/24/outline";
import React, { useRef, useState } from "react";
import { useUploadFile } from "~/hooks/useFiles";
import type { FileType } from "~/types/file";

const FileUploader = () => {
  const uploadFileMutation = useUploadFile();
  const [selectedFileType, setSelectedFileType] =
    useState<FileType>("passport");
  const fileInputRef = useRef<HTMLInputElement>(null);

  const handleFileSelect = (event: React.ChangeEvent<HTMLInputElement>) => {
    const fileList = event.target.files;
    if (!fileList || fileList.length === 0) return;

    const file = fileList[0];

    uploadFileMutation.mutate({
      file: file,
      fileType: selectedFileType,
    });

    if (fileInputRef.current) {
      fileInputRef.current.value = "";
    }
  };

  return (
    <div className="bg-white border border-gray-100 px-6 py-8 rounded-xl max-w-5xl mx-auto w-full">
      <div className="flex flex-col items-center justify-between mb-6">
        <div className="text-xl font-bold text-black">
          Upload your essential documents
        </div>
        <div className="text-sm text-gray-500">
          Please upload the documents that need to be reviewed.
        </div>
      </div>

      <div className="max-w-xs mx-auto">
        <div className="mb-4">
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Document Type
          </label>
          <select
            className="w-full px-3 text-base py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            value={selectedFileType}
            onChange={(e) => setSelectedFileType(e.target.value as FileType)}
          >
            <option value="passport">Passport</option>
            <option value="utility_bill">Utility Bill</option>
            <option value="other">Other</option>
          </select>
        </div>

        <div className="text-center">
          <input
            type="file"
            ref={fileInputRef}
            className="hidden"
            onChange={handleFileSelect}
            accept="image/*,application/pdf"
          />
          <button
            onClick={() => fileInputRef.current?.click()}
            disabled={uploadFileMutation.isPending}
            className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <ArrowUpTrayIcon className="h-4 w-4 mr-2" />
            {uploadFileMutation.isPending ? "Uploading..." : "Upload File"}
          </button>
          <p className="text-xs text-gray-500 mt-2">
            Supported formats: PDF, JPG, PNG, <br /> max size 4MB
          </p>
        </div>
      </div>
    </div>
  );
};

export default FileUploader;
