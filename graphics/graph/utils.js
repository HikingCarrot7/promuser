function secondsToHms(seconds) {
  seconds = Number(seconds);
  const { h, m, s } = extractHmsFromSeconds(seconds);

  function format(x) {
    return ('0' + x).slice(-2);
  }

  if (h <= 0) {
    return `00:${format(m)}:${format(s)}`;
  }

  return `${h}:${format(m)}:${format(s)}`;
}

function extractHmsFromSeconds(seconds) {
  return {
    h: Math.floor(seconds / 3600),
    m: Math.floor((seconds % 3600) / 60),
    s: Math.floor((seconds % 3600) % 60),
  };
}

function calculateDiameterForNode(x, min, max) {
  if (min === max) {
    return MIN_NODE_DIAMETER;
  }
  return (
    ((x - min) / (max - min)) * (MAX_NODE_DIAMETER - MIN_NODE_DIAMETER) +
    MIN_NODE_DIAMETER
  );
}
