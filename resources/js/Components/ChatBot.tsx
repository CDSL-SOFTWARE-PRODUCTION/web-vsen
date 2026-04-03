import { useState } from 'react';

export default function ChatBot() {
    const [isOpen, setIsOpen] = useState(false);

    return (
        <div className="fixed bottom-6 right-6 z-50">
            {isOpen ? (
                <div className="bg-white rounded-lg shadow-xl border border-gray-200 w-80 overflow-hidden flex flex-col">
                    <div className="bg-ge-blue text-white p-4 flex justify-between items-center">
                        <span className="font-bold">VSENS Support</span>
                        <button onClick={() => setIsOpen(false)} className="text-white hover:text-gray-200">
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div className="p-4 h-64 bg-gray-50 flex flex-col items-center justify-center text-gray-500 text-sm">
                        <p>Chat is currently unavailable.</p>
                        <p className="mt-2">Please contact us via email.</p>
                    </div>
                </div>
            ) : (
                <button
                    onClick={() => setIsOpen(true)}
                    className="bg-ge-blue text-white p-4 rounded-full shadow-lg hover:bg-blue-800 transition-all hover:scale-110"
                >
                    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                </button>
            )}
        </div>
    );
}
