import type { UploadedFile } from "../types/file";
import type { Route } from "./+types/home";

import { useState } from "react";
import { Toaster } from "sonner";
import FileUploader from "~/components/FileUploader";
import FilesContent from "~/components/FilesContent";

export function meta({}: Route.MetaArgs) {
  return [
    { title: "File uploader" },
    { name: "description", content: "Welcome to React Router!" },
  ];
}

export default function Home() {
  const [isPreviewOpen, setIsPreviewOpen] = useState<boolean>(false);
  const [previewingFile, setPreviewingFile] = useState<UploadedFile | null>(
    null
  );

  return (
    <div className="min-h-screen flex flex-col items-center justify-center bg-gray-100 gap-16">
      <Toaster richColors />
      <FileUploader />
      <FilesContent />
    </div>
  );
}
