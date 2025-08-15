function confirmDelete(path) {
    Swal.fire({
        title: "ลบข้อมูล ?",
        text: "คุณต้องการลบข้อมูลหรือไม่ !",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "ยกเลิก",
        confirmButtonText: "ยืนยัน, ลบข้อมูล!"
    }).then((result) => {
        if (result.isConfirmed) {
            location.href = path
            Swal.fire({
                title: "เสร็จสิ้น !",
                text: "ลบข้อมูลเสร็จสิ้น.",
                icon: "success"
            });
        }
    });
}