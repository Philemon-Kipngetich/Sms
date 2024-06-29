document.getElementById("program").addEventListener("change", function () {
  const program = this.value;
  const year = new Date().getFullYear();
  const constantDigits = "20";
  const randomNumbers = Math.floor(Math.random() * 900) + 100;
  const admissionNumber = `B${
    program === "IT" ? "141" : "135"
  }/${constantDigits}${randomNumbers}/${year}`;

  document.getElementById("admission-number").value = admissionNumber;
});

