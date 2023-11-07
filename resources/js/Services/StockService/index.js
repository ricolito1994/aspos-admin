export const convertQuantity = (
    unitArray, // array of units of the specified product
    quantity, // quantity to be converted
    unitObj // actual unit of this transaction
) => {
    let selUnit = unitArray.find(x => x.id === unitObj.id)
    let j = unitObj.heirarchy - 1;
    let cdn = selUnit.heirarchy >= unitObj.heirarchy;
    let ctr =  selUnit.heirarchy - 1 ;
    while ( cdn ? ctr >= j : ctr <= j ) {
        let sUnit = unitArray[j];
        // if unit of remaining balance 
        // is less than of the selected unit of qty
        if (!cdn) {
            quantity /= parseFloat(sUnit.parent_quantity);
            j--;
        } else {
            if (sUnit.heirarchy !== unitObj.heirarchy) {
                quantity *= parseFloat(sUnit.parent_quantity);
            }
            j++;
        }
        if (sUnit.heirarchy == selUnit.heirarchy) {
            break;
        }
    }
    return quantity;
}