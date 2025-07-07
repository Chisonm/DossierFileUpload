import { useState } from "react";
import { Tab } from "@headlessui/react";
import { DocumentTextIcon } from "@heroicons/react/24/outline";
import type { FileType, UploadedFile } from "~/types/file";
import { useDossierFiles } from "~/hooks/useFiles";
import FileItem from "./FileItem";
import LoaderOverlay from "./loaders/LoaderOverlay";

interface FileGroup {
  id: FileType;
  name: string;
  icon: React.ReactElement;
}

const FilesContent = () => {
  const { data: files, isLoading, isFetching } = useDossierFiles();
  const [selectedIndex, setSelectedIndex] = useState<number>(0);

  const getFilesForGroup = (groupId: string): UploadedFile[] => {
    if (!files) return [];

    switch (groupId) {
      case "passport":
        return files.passport || [];
      case "utility_bill":
        return files.utility_bill || [];
      case "other":
        return files.other || [];
      default:
        return [];
    }
  };

  const getFileCount = (groupId: string): number => {
    return getFilesForGroup(groupId).length;
  };

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

  const renderFileList = (files: UploadedFile[], groupName: string) => {
    if (!files || files.length === 0) {
      return (
        <div className="text-center py-16 text-gray-500">
          <DocumentTextIcon className="h-12 w-12 mx-auto mb-4 text-gray-400" />
          No {groupName} files uploaded yet
        </div>
      );
    }

    return (
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {files.map((file) => (
          <FileItem key={file.id} file={file} />
        ))}
      </div>
    );
  };

  if (isLoading) {
    return (
      <div className="h-[334px] w-full max-w-5xl bg-gray-200 rounded-xl animate-pulse"></div>
    );
  }

  return (
    <div className="bg-white border border-gray-100 p-3 rounded-xl max-w-5xl mx-auto w-full relative overflow-hidden">
      {isFetching ? <LoaderOverlay text="Fetching files..." /> : null}
      <Tab.Group selectedIndex={selectedIndex} onChange={setSelectedIndex}>
        <Tab.List className="flex space-x-1 rounded-xl bg-gray-100 p-1">
          {fileGroups.map((group) => (
            <Tab
              key={group.id}
              className={({ selected }) =>
                `w-full rounded-lg py-2.5 text-sm font-medium leading-5 transition-colors duration-200 cursor-pointer
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
  );
};

export default FilesContent;
