const API_BASE = import.meta.env.VITE_API_URL || 'https://localhost/api';

export async function sendErrorToBackend(error, extraInfo = '') {
  try {
    await fetch(`${API_BASE}/client_logs`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/ld+json',
        'Accept': 'application/ld+json',
      },
      body: JSON.stringify({
        message: typeof error === 'string' ? error : (error.message || 'Unknown Frontend Error'),
        stackTrace: error.stack ? `${error.stack}\n${extraInfo}` : extraInfo,
        url: window.location.href,
        userAgent: navigator.userAgent,
      }),
    });
  } catch (err) {
    console.error('Failed to log error to backend:', err);
  }
}
