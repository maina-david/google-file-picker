import { Head, useForm } from '@inertiajs/react'
import { useState, useEffect } from 'react'
import useDrivePicker from 'react-google-drive-picker'

export default function Welcome() {
    const [openPicker, authResponse] = useDrivePicker()
    const [selectedFiles, setSelectedFiles] = useState([])

    const { data, setData, post, processing, errors, wasSuccessful, clearErrors } = useForm({
        files: [],
    })

    const handleAuthResponse = () => {
        if (authResponse && authResponse.access_token) {
            localStorage.setItem('google_access_token', authResponse.access_token)
            localStorage.setItem('google_token_expiry', Date.now() + authResponse.expires_in * 1000)
        } else {
            console.error('Authentication failed or not yet provided')
        }
    }

    useEffect(() => {
        handleAuthResponse()
    }, [authResponse])

    const getStoredToken = () => {
        const token = localStorage.getItem('google_access_token')
        const expiry = localStorage.getItem('google_token_expiry')

        if (token && expiry && Date.now() < Number(expiry)) {
            return token
        } else {
            localStorage.removeItem('google_access_token')
            localStorage.removeItem('google_token_expiry')
            return null
        }
    }

    const handleOpenPicker = () => {
        const token = getStoredToken()

        openPicker({
            clientId: import.meta.env.VITE_GOOGLE_CLIENT_ID,
            developerKey: import.meta.env.VITE_GOOGLE_API_KEY,
            viewId: "DOCS",
            token: token,
            showUploadView: true,
            showUploadFolders: true,
            supportDrives: true,
            multiselect: true,
            callbackFunction: (data) => {
                if (data.action === 'picked') {
                    setSelectedFiles(data.docs)
                    setData('files', data.docs)
                }
            },
        })
    }

    const handleSubmit = (e) => {
        e.preventDefault()
        clearErrors()
        post('/upload', {
            ...data,
            onSuccess: () => {
                setSelectedFiles([])
            },
        })
    }

    return (
        <>
            <Head title="Welcome" />
            <div className="min-h-screen flex flex-col items-center justify-center bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100">
                <div className="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-8 max-w-md w-full text-center">
                    <h1 className="text-2xl font-semibold mb-4">Google Drive Picker</h1>
                    <p className="text-gray-600 dark:text-gray-400 mb-6">
                        Open the Google Drive Picker to select or upload your files.
                    </p>
                    <button
                        onClick={handleOpenPicker}
                        className="bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 mb-6"
                    >
                        Open Picker
                    </button>

                    {selectedFiles.length > 0 && (
                        <div className="w-full grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            {selectedFiles.map((file, index) => (
                                <div key={index} className="p-4 border rounded-lg bg-gray-50 dark:bg-gray-700">
                                    <p className="font-semibold">Name: {file.name}</p>
                                    <p className="text-sm text-gray-500">Type: {file.mimeType}</p>
                                    <a
                                        href={file.url}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="text-blue-600 dark:text-blue-400 hover:underline"
                                    >
                                        View File
                                    </a>
                                    <p className="text-sm text-gray-500">Last Edited: {new Date(file.lastEditedUtc).toLocaleString()}</p>
                                    {file.isShared && (
                                        <p className="text-xs text-gray-500">Shared by: {file.organizationDisplayName}</p>
                                    )}
                                </div>
                            ))}
                        </div>
                    )}

                    <form onSubmit={handleSubmit}>
                        {selectedFiles.length > 0 && (
                            <button
                                type="submit"
                                className="bg-green-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50"
                                disabled={processing}
                            >
                                Submit Files
                            </button>
                        )}

                        {processing && (
                            <progress value={processing.percentage} max="100" className="mt-4 w-full">
                                {processing.percentage}%
                            </progress>
                        )}

                        {Object.keys(errors).length > 0 && (
                            <div className="mt-4 text-red-500">
                                {Object.values(errors).map((error, index) => (
                                    <div key={index}>{error}</div>
                                ))}
                            </div>
                        )}

                        {wasSuccessful && (
                            <div className="mt-4 text-green-500">
                                Files uploaded successfully!
                            </div>
                        )}
                    </form>
                </div>
            </div>
        </>
    )
}
