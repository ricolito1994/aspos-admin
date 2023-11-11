export const convertQuantity = (
    unitArray,
    quantity,
    unitObj,
) => {
    let selUnit = unitArray.find(x => x.id === unitObj.id)
    let j = unitObj.heirarchy - 1;
    let cdn = selUnit.heirarchy >= unitObj.heirarchy;
    let ctr =  selUnit.heirarchy - 1 ;
    while ( cdn ? ctr >= j : ctr <= j ) {
        let sUnit = unitArray[j];
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