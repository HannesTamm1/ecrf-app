const Ziggy = {"url":"http:\/\/localhost","port":null,"defaults":{},"routes":{"upload":{"uri":"\/","methods":["GET","HEAD"]},"wizard":{"uri":"wizard","methods":["GET","HEAD"]},"output":{"uri":"output","methods":["GET","HEAD"]},"storage.local":{"uri":"storage\/{path}","methods":["GET","HEAD"],"wheres":{"path":".*"},"parameters":["path"]}}};
if (typeof window !== 'undefined' && typeof window.Ziggy !== 'undefined') {
  Object.assign(Ziggy.routes, window.Ziggy.routes);
}
export { Ziggy };
