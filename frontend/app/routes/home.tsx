import type {
  DossierFilesResponse,
  UploadedFile,
  FileType,
  DossierFiles,
} from "../types/file";
import {
  useDossierFiles,
  useUploadFile,
  useDeleteFile,
} from "../hooks/useFiles";
import type { Route } from "./+types/home";
import { 
  DocumentTextIcon, 
  TrashIcon, 
  EyeIcon, 
  ArrowUpTrayIcon,
  XMarkIcon 
} from "@heroicons/react/24/outline";
import { Tab } from "@headlessui/react";
import { useState, useRef, useEffect } from "react";

export function meta({}: Route.MetaArgs) {
  return [
    { title: "New React Router App" },
    { name: "description", content: "Welcome to React Router!" },
  ];
}

interface FileGroup {
  id: FileType;
  name: string;
  icon: React.ReactElement;
}

export default function Home() {
  const { data: files, isLoading, error } = useDossierFiles();
  const uploadFileMutation = useUploadFile();
  const deleteFileMutation = useDeleteFile();
  const [selectedIndex, setSelectedIndex] = useState<number>(0);
  const [selectedFileType, setSelectedFileType] = useState<FileType>("passport");
  
  const fileInputRef = useRef<HTMLInputElement>(null);
  
  // State for file preview modal
  const [isPreviewOpen, setIsPreviewOpen] = useState<boolean>(false);
  const [previewingFile, setPreviewingFile] = useState<UploadedFile | null>(null);

  // Handle file selection and immediate upload
  const handleFileSelect = (event: React.ChangeEvent<HTMLInputElement>) => {
    const fileList = event.target.files;
    if (!fileList || fileList.length === 0) return;
    
    const file = fileList[0];
    
    // Upload immediately
    uploadFileMutation.mutate({ 
      file: file, 
      fileType: selectedFileType 
    });
    
    // Reset file input
    if (fileInputRef.current) {
      fileInputRef.current.value = '';
    }
  };
  
  // Open preview modal for a specific file
  const openPreview = (file: UploadedFile) => {
    setPreviewingFile(file);
    setIsPreviewOpen(true);
  };
  
  // Handle file deletion
  const handleDeleteFile = (fileId: string) => {
    deleteFileMutation.mutate(fileId);
  };

  const getTotalFileCount = (files: DossierFiles) => {
    if (!files) return 0;
    return (
      (files.passport?.length || 0) + 
      (files.utility_bill?.length || 0) + 
      (files.other?.length || 0)
    );
  };

  const getFilesForGroup = (groupId: string): UploadedFile[] => {
    if (!files) return [];
    
    switch (groupId) {
      case 'passport':
        return files.passport || [];
      case 'utility_bill':
        return files.utility_bill || [];
      case 'other':
        return files.other || [];
      default:
        return [];
    }
  };

  const getFileCount = (groupId: string): number => {
    return getFilesForGroup(groupId).length;
  };
  
  // File type groups with display names and icons
  const fileGroups: FileGroup[] = [
    {
      id: "passport",
      name: "Passport",
      icon: <DocumentTextIcon className="h-5 w-5 text-primary-600" />,
    },
    {
      id: "utility_bill",
      name: "Utility Bill",
      icon: <DocumentTextIcon className="h-5 w-5 text-secondary-600" />,
    },
    {
      id: "other",
      name: "Other Documents",
      icon: <DocumentTextIcon className="h-5 w-5 text-gray-600" />,
    },
  ];

  // Render file list for each file type
  const renderFileList = (files: UploadedFile[], groupName: string) => {
    if (!files || files.length === 0) {
      return (
        <div className="text-center py-10 text-gray-500">
          No {groupName} files uploaded yet
        </div>
      );
    }

    return (
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {files.map((file) => (
          <div key={file.id} className="border rounded-lg p-4 bg-gray-50">
            <div className="flex justify-between items-start">
              <div>
                <h3 className="font-medium text-gray-900 truncate">{file.originalName}</h3>
                <p className="text-sm text-gray-500">{(file.size / 1024).toFixed(2)} KB</p>
                <p className="text-xs text-gray-400">{new Date(file.uploadedAt).toLocaleString()}</p>
              </div>
              <div className="flex space-x-2">
                <button
                  onClick={() => openPreview(file)}
                  className="text-primary-600 hover:text-primary-800"
                  title="Preview file"
                >
                  <EyeIcon className="h-5 w-5" />
                </button>
                <button
                  onClick={() => handleDeleteFile(file.id)}
                  className="text-red-500 hover:text-red-700"
                  title="Delete file"
                >
                  <TrashIcon className="h-5 w-5" />
                </button>
              </div>
            </div>
            <div className="mt-2">
              <a 
                href={file.url} 
                target="_blank" 
                rel="noopener noreferrer"
                className="text-primary-600 hover:text-primary-800 text-sm flex items-center"
              >
                <DocumentTextIcon className="h-4 w-4 mr-1" />
                View Document
              </a>
            </div>
          </div>
        ))}
      </div>
    );
  };
  
  // File Preview Modal Component
  const FilePreviewModal = () => {
    if (!isPreviewOpen || !previewingFile) return null;
    
    // Add null checks to prevent errors
    const isImage = previewingFile?.mimetype ? previewingFile.mimetype.startsWith('image/') : false;
    const isPdf = previewingFile?.mimetype === 'application/pdf';
    
    return (
      <div className="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
        <div className="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">
          <div className="p-4 border-b flex justify-between items-center">
            <h3 className="font-medium">{previewingFile?.originalName || 'File Preview'}</h3>
            <button 
              onClick={() => setIsPreviewOpen(false)}
              className="text-gray-500 hover:text-gray-700"
            >
              <XMarkIcon className="h-6 w-6" />
            </button>
          </div>
          
          <div className="flex-1 overflow-auto p-4">
            {isImage && previewingFile?.url && (
              <img 
                src={previewingFile.url} 
                alt={previewingFile.originalName || 'Image preview'}
                className="max-w-full max-h-[70vh] mx-auto"
              />
            )}
            {isPdf && previewingFile?.url && (
              <iframe
                src={`${previewingFile.url}#toolbar=0`}
                className="w-full h-[70vh]"
                title={previewingFile.originalName || 'PDF preview'}
              />
            )}
            {(!isImage && !isPdf) && (
              <div className="text-center py-10">
                <p>Preview not available for this file type</p>
                {previewingFile?.url && (
                  <a 
                    href={previewingFile.url} 
                    download={previewingFile.originalName || 'download'}
                    className="mt-4 inline-block bg-primary-600 text-white px-4 py-2 rounded"
                  >
                    Download File
                  </a>
                )}
              </div>
            )}
          </div>
        </div>
      </div>
    );
  };


  // Handle error state
  if (error) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center text-red-600">
          <p>Error loading documents: {error.message}</p>
          <button 
            onClick={() => window.location.reload()} 
            className="mt-2 bg-primary-600 text-white px-4 py-2 rounded"
          >
            Retry
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen flex flex-col items-center justify-center bg-gray-100 gap-16">
      <div className="bg-white border border-gray-200 p-6 rounded-xl max-w-5xl mx-auto w-full">
        <div className="flex flex-col items-center justify-between mb-6">
          <div className="text-sm font-bold text-black">
            Upload your essential documents
          </div>
          <div className="text-sm text-gray-500">
            Please upload the documents that need to be reviewed.
          </div>
        </div>
        
        {/* Minimal Upload Section */}
        <div className="max-w-md mx-auto">
          <div className="mb-4">
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Document Type
            </label>
            <select 
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
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
              {uploadFileMutation.isPending ? 'Uploading...' : 'Upload File'}
            </button>
            <p className="text-xs text-gray-500 mt-2">
              Supported formats: PDF, JPG, PNG
            </p>
          </div>
          
          {/* Upload Status Messages */}
          {uploadFileMutation.isError && (
            <div className="mt-4 text-center text-red-600 text-sm">
               {uploadFileMutation.error.message}
            </div>
          )}
          {uploadFileMutation.isSuccess && (
            <div className="mt-4 text-center text-green-600 text-sm">
              File uploaded successfully!
            </div>
          )}
        </div>
      </div>

      <div className="bg-white border border-gray-200 p-3 rounded-xl max-w-5xl mx-auto w-full">
        <Tab.Group selectedIndex={selectedIndex} onChange={setSelectedIndex}>
          <Tab.List className="flex space-x-1 rounded-xl bg-gray-100 p-1">
            {fileGroups.map((group) => (
              <Tab
                key={group.id}
                className={({ selected }) =>
                  `w-full rounded-lg py-2.5 text-sm font-medium leading-5 transition-colors duration-200
          ${
            selected
              ? "bg-white shadow text-primary-700"
              : "text-gray-600 hover:bg-white/[0.12] hover:text-gray-700"
          }`
                }
              >
                <div className="flex items-center justify-center space-x-2">
                  {group.icon}
                  <span>{group.name}</span>
                  <span className="inline-flex items-center justify-center w-6 h-6 text-xs font-bold leading-none text-white bg-primary-600 rounded-full">
                    {getFileCount(group.id)}
                  </span>
                </div>
              </Tab>
            ))}
          </Tab.List>

          <Tab.Panels className="mt-4">
            {fileGroups.map((group) => (
              <Tab.Panel
                key={group.id}
                className={`rounded-xl p-3 focus:outline-none`}
              >
                {renderFileList(getFilesForGroup(group.id), group.name)}
              </Tab.Panel>
            ))}
          </Tab.Panels>
        </Tab.Group>
      </div>
      
      {/* File Preview Modal */}
      <FilePreviewModal />
    </div>
  );
}