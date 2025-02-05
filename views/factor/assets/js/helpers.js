function formatAsMoney(number) {
  return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function removeLeadingZeros(numberStr) {
  // Convert the input to a string
  numberStr = String(numberStr);

  // If the input is an empty string, return '0'
  if (numberStr === "") {
    return "0";
  }

  // Replace leading zeros
  const result = numberStr.replace(/^0+/, "");

  // If the result is an empty string after replacing zeros, return '0'
  return result === "" ? "0" : result;
}

function numberToPersianWords(number) {
  const units = [
    "", // ones
    "هزار", // thousands
    "میلیون", // millions
    "میلیارد", // billions
    "تریلیارد", // trillions
    "پادا", // quadrillions
    "هکتا", // quintillions
    "اکتا", // sextillions
    "نونا", // septillions
    "دسیلیارد", // decillions
  ];

  const numberStr = String(number).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  const chunks = numberStr.split(",");

  let words = [];
  const size = chunks.length;

  for (let index in chunks) {
    let chunk = removeLeadingZeros(chunks[index]);
    if (chunk.length > 0) {
      let word = converter(chunk);
      if (word.length > 0) {
        if (units[size - (Number(index) + 1)]) {
          word += " " + units[size - (Number(index) + 1)];
        }
        words.push(word);
      }
    }
  }

  return words.join(" و ") + " ریال";
}

function converter(chunk) {
  const ones = ["", "یک", "دو", "سه", "چهار", "پنج", "شش", "هفت", "هشت", "نه"];
  const teens = [
    "ده",
    "یازده",
    "دوازده",
    "سیزده",
    "چهارده",
    "پانزده",
    "شانزده",
    "هفده",
    "هجده",
    "نوزده",
  ];
  const tens = [
    "",
    "",
    "بیست",
    "سی",
    "چهل",
    "پنجاه",
    "شصت",
    "هفتاد",
    "هشتاد",
    "نود",
  ];
  const hundreds = [
    "",
    "صد",
    "دویست",
    "سیصد",
    "چهارصد",
    "پانصد",
    "ششصد",
    "هفتصد",
    "هشتصد",
    "نهصد",
  ];

  let num = parseInt(chunk, 10);
  if (num === 0) return "";

  let words = [];

  if (num >= 100) {
    words.push(hundreds[Math.floor(num / 100)]);
    num %= 100;
  }

  if (num >= 10 && num <= 19) {
    words.push(teens[num - 10]);
  } else {
    if (num >= 20) {
      words.push(tens[Math.floor(num / 10)]);
      num %= 10;
    }
    if (num > 0) {
      words.push(ones[num]);
    }
  }

  return words.join(" و ");
}

// display the bill total amount alphabetically -------------- END

function convertToEnglishNumbers(value) {
  const englishCharMap = {
    "۱": "1",
    "۲": "2",
    "۳": "3",
    "۴": "4",
    "۵": "5",
    "۶": "6",
    "۷": "7",
    "۸": "8",
    "۹": "9",
    "۰": "0",
  };

  const customInput = value;
  let customText = "";
  const inputText = value;
  for (let i = 0; i < inputText.length; i++) {
    const char = inputText[i];
    if (char in englishCharMap) {
      customText += englishCharMap[char];
    } else {
      customText += char;
    }
  }
  return customText;
}

function saveAsPDF() {
  const content = document.getElementById("bill_body_pdf");
  const opt = {
    filename: "customer_bill.pdf",
    image: {
      type: "jpeg",
      quality: 0.98,
    },
    html2canvas: {
      scale: 2,
    },
    jsPDF: {
      unit: "in",
      format: "letter",
      orientation: "portrait",
    },
  };
  html2pdf().set(opt).from(content).save();
}
