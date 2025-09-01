const requireContext = require.context('./blocks', true, /index\.js$/);
requireContext.keys().forEach(requireContext);
