function secondsToHms(seconds) {
  seconds = Number(seconds);

  const h = Math.floor(seconds / 3600);
  const m = Math.floor((seconds % 3600) / 60);
  const s = Math.floor((seconds % 3600) % 60);

  if (h <= 0) {
    return `00:${format(m)}:${format(s)}`;
  }

  return `${h}:${format(m)}:${format(s)}`;
}

function format(x) {
  return ('0' + x).slice(-2);
}

function calculateDiameterForNode(x, min, max) {
  if (min === max) {
    return MIN_DIAMETER;
  }
  return (
    ((x - min) / (max - min)) * (MAX_DIAMETER - MIN_DIAMETER) + MIN_DIAMETER
  );
}
