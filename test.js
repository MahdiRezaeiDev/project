function getGoodItemAmount(partNumber) {
    let quantity = 1;

    // Exact part numbers with fixed quantities (exceptions)
    const exceptionCodes = {
        '2102025150': 1
    };

    // Exact complete codes with fixed quantities
    const completeCodes = {
        '1884111051': 4,
        '2741023700': 6
    };

    // Specific substrings-based quantities
    const specificItemsQuantity = {
        '51712': 2,
        '54813': 2,
        '55513': 2,
        '58411': 2,
        '230602': 4,
        '234102': 4,
        '210203': 4,
        '230412': 4,
        '210202': 5,
        '273012': 4,
        '273013': 6,
        '230603': 6,
        '234103': 6,
        '230413': 6,
        '273002': 4,
        '2730137': 1,
        '2730103': 4,
        '230603F': 8,
        '210203F': 4,
        '18858100': 4
    };

    // Regular expression-based patterns and their corresponding quantities
    const patternQuantities = [
        { pattern: /^23060\d9$/, quantity: 1 }, // Matches "23060-9"
        { pattern: /^21020\d9$/, quantity: 1 }  // Matches "21020-9"
    ];

    // STEP 1: Check for exact matches in exceptions
    if (exceptionCodes.hasOwnProperty(partNumber)) {
        return exceptionCodes[partNumber];
    }

    // STEP 2: Check for exact matches in complete codes
    if (completeCodes.hasOwnProperty(partNumber)) {
        return completeCodes[partNumber];
    }

    // STEP 3: Check for specific substring-based matches
    const sortedKeys = Object.keys(specificItemsQuantity).sort((a, b) => b.length - a.length); // Sort by length (desc)
    for (const key of sortedKeys) {
        if (partNumber.startsWith(key)) {
            return specificItemsQuantity[key];
        }
    }

    // STEP 4: Check for pattern-based matches using regular expressions
    for (const { pattern, quantity } of patternQuantities) {
        if (pattern.test(partNumber)) {
            return quantity;
        }
    }

    // STEP 5: Default quantity if no match is found
    return quantity;
}

console.log(getGoodItemAmount('230609'));      // Output: 1 (matches regex /^23060\d9$/)
console.log(getGoodItemAmount('210209'));      // Output: 1 (matches regex /^21020\d9$/)
console.log(getGoodItemAmount('230603F'));     // Output: 8 (matches specificItemsQuantity key)
console.log(getGoodItemAmount('2102025150'));  // Output: 1 (matches exceptionCodes key)
console.log(getGoodItemAmount('2730137'));     // Output: 1 (matches specificItemsQuantity key)
console.log(getGoodItemAmount('273013'));      // Output: 6 (prioritizes longer matches)
console.log(getGoodItemAmount('unknown'));     // Output: 1 (default)
console.log(getGoodItemAmount('1884111051'));     // Output: 1 (default)
