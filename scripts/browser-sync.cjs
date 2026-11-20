// Studio sends chunked HTML. BrowserSync buffers it and sets Content-Length.
module.exports = {
  proxy: {
    target: process.env.KEKIDI_STUDIO_URL || 'http://localhost:8885',
    proxyRes: [response => { delete response.headers['transfer-encoding']; }],
  },
};
