function tabs_open(windows) {
  function sleep(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
  }

  async function display() {
    for (let i = 0; i < windows.length - 1; i++) {
      window.open(windows[i]);
      await sleep(200);
    }
    window.location.assign(windows[windows.length - 1]);
  }

  display();

  return true;
}
