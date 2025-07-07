import { ArrowPathIcon } from "@heroicons/react/24/outline";

interface LoaderOverlayProps {
  text?: string;
}
const LoaderOverlay = ({ text = "Loading..." }: LoaderOverlayProps) => {
  return (
    <div className="absolute w-full h-full inset-0 bg-white/25 z-10 backdrop-blur-sm flex items-center justify-center">
      <div className="flex items-center justify-center w-full">
        <ArrowPathIcon className="animate-spin text-blue-500 size-4" />
        <span className="ml-2 text-sm text-gray-600">{text}</span>
      </div>
    </div>
  );
};

export default LoaderOverlay;
