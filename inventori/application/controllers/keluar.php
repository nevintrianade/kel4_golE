<?php
defined('BASEPATH') OR exit('No direct script access allowed');  
 
class Keluar extends CI_Controller { 

	
	//load library, helper, dan model
	function __construct(){  
		parent::__construct(); 
		$this->load->helper('url'); 
		$this->load->helper('html'); 
        $this->load->helper(array('form', 'url')); 
		$this->load->model('m_barang');
		$this->load->library('cetak_pdf');
	}
 //menampilkan barang pada home
	public function index(){	
		$this->load->view('v_header'); 
        $this->load->view('v_sidebar'); 
        $this->load->view('v_keluar'); 
	}
	
	public function tambah()
	{
		$this->load->view('v_header'); 
        $this->load->view('v_sidebar'); 
		$this->load->model('m_nourut');
		$data = array(

			'kode' => $this->m_nourut->buat_kode_penjualan(),
			
		);
		$this->load->view('v_keluar1',$data);
	}


	public function cek_barang()
	{
        $id_barang = $this->input->post('id_barang');
        $cek = $this->db->query("SELECT * FROM barang WHERE id_barang='$id_barang'")->row();
		$data = array(
			'stok' => $cek->stok,
			'harga' => $cek->harga,
			'id_barang' => $cek->id_barang,
			'nama_barang' => $cek->nama_barang,
		);
		echo json_encode($data);
	}


	public function simpan_cart()
	{
		
        $data = array(
            'id'    => $this->input->post('id_barang'),
            'qty'   => $this->input->post('jumlah'),
            'price' => $this->input->post('harga'),
            'name'  => $this->input->post('nabar'),
        );
        $this->cart->insert($data);
        redirect('keluar/tambah');
	}

	public function hapus_cart($id)
	{
		
        $data = array(
            'rowid'    => $id,
            'qty'   => 0,
        );
        $this->cart->update($data);
        redirect('keluar/tambah');
	}

	public function simpan_penjualan()
	{
        $kode_penjualan = $this->input->post('kode_penjualan');
        $total_keluar = $this->input->post('total_keluar');
        $tgl_penjualan = $this->input->post('tgl_penjualan');
        $id_user = $this->input->post('id_user');

        $data = array(
        	
            'id_keluar'=> $kode_penjualan,
            'total_keluar'=> $total_keluar,
			'tgl_keluar'=> $tgl_penjualan,
			'id_user'=> $id_user,
			
        );
        $this->db->insert('keluar', $data);

	
	foreach ($this->cart->contents() as $items) {
		$id_barang = $items['id'];
		$qty_keluar = $items['qty'];
		$d = array(
			'id_keluar' => $kode_penjualan,
			'id_barang' => $id_barang,
			'qty_keluar' => $qty_keluar,
			
		);
		$this->db->insert('detail_keluar', $d);
		//$this->db->query("UPDATE menu SET satuan=satuan-'$qty_keluar' WHERE kode_menu='$id_barang'");
	} 
	$this->cart->destroy();
	redirect('keluar');
}


public function hapus_penjualan($kode_penjualan)
	{
		
        $this->db->where('id_keluar', $kode_penjualan);
		$this->db->delete('keluar');
		$this->db->where('id_keluar', $kode_penjualan);
		$this->db->delete('detail_keluar');
		?>
		<script type="text/javascript">
			alert('Berhapus Hapus Data');
			window.location='<?php echo base_url('keluar') ?>';
		</script>
		<?php
	}

	public function cetak_penjualan($kode_penjualan)
	{
		
        $data = array(
			'data' => $this->db->query("SELECT * FROM keluar inner join user on user.id_user=keluar.id_user where id_keluar='$kode_penjualan'"),
		);
		$this->load->view('v_keluar3',$data);
	}

	public function detail_penjualan($kode_penjualan)
	{
		$this->load->view('v_header'); 
        $this->load->view('v_sidebar'); 
		$data = array(

			'data' => $this->db->query("SELECT * FROM keluar inner join user on user.id_user=keluar.id_user where id_keluar='$kode_penjualan'"),
		);
		$this->load->view('v_keluar2',$data);
	}

	public function cetak_pdf() {

        $pdf = new FPDF('P', 'mm','Letter');

        $pdf->AddPage();

        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(0,7,'TRANSAKSI BARANG KELUAR',0,1,'C');
        $pdf->Cell(10,7,'',0,1);

        $pdf->SetFont('Arial','B',10);

        $pdf->Cell(8,6,'No',1,0,'C');
        $pdf->Cell(30,6,'Kode Transaksi',1,0,'C');
        $pdf->Cell(50,6,'Tgl Transaksi',1,0,'C');
        $pdf->Cell(50,6,'Total bayar',1,0,'C');
        $pdf->Cell(60,6,'Nama Pelanggan',1,1,'C');
 
        $pdf->SetFont('Arial','',10);
        $barang= $this->db->query("SELECT * FROM keluar inner join user on keluar.id_user=user.id_user")->result();
        $no=1;
        foreach ($barang as $data){
            $pdf->Cell(8,6,$no,1,0);
            $pdf->Cell(30,6,$data->id_keluar,1,0);
            $pdf->Cell(50,6,$data->tgl_keluar,1,0);
            $pdf->Cell(50,6,"Rp ".number_format($data->total_keluar, 0, ".", "."),1,0);
            $pdf->Cell(60,6,$data->nama,1,1);
           
            $no++;
        }
        $pdf->Output();
	

	}
	

	public function cetak_pdf1() {

        $pdf = new FPDF('P', 'mm','Letter');

        $pdf->AddPage();

        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(0,7,'TRANSAKSI BARANG KELUAR',0,1,'C');
        $pdf->Cell(10,7,'',0,1);

        $pdf->SetFont('Arial','B',10);

        $pdf->Cell(8,6,'No',1,0,'C');
        $pdf->Cell(30,6,'Kode Transaksi',1,0,'C');
        $pdf->Cell(50,6,'Tgl Transaksi',1,0,'C');
        $pdf->Cell(50,6,'Total bayar',1,0,'C');
        $pdf->Cell(60,6,'Nama Pelanggan',1,1,'C');
 
        $pdf->SetFont('Arial','',10);
        $barang= $this->db->query("SELECT * FROM keluar inner join user on keluar.id_user=user.id_user where date(keluar.tgl_keluar)=date(now())")->result();
        $no=1;
        foreach ($barang as $data){
            $pdf->Cell(8,6,$no,1,0);
            $pdf->Cell(30,6,$data->id_keluar,1,0);
            $pdf->Cell(50,6,$data->tgl_keluar,1,0);
            $pdf->Cell(50,6,"Rp ".number_format($data->total_keluar, 0, ".", "."),1,0);
            $pdf->Cell(60,6,$data->nama,1,1);
           
            $no++;
        }
        $pdf->Output();
	

	}
	
	public function cetak_pdf2() {

        $pdf = new FPDF('P', 'mm','Letter');

        $pdf->AddPage();

        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(0,7,'TRANSAKSI BARANG KELUAR',0,1,'C');
        $pdf->Cell(10,7,'',0,1);

        $pdf->SetFont('Arial','B',10);

        $pdf->Cell(8,6,'No',1,0,'C');
        $pdf->Cell(30,6,'Kode Transaksi',1,0,'C');
        $pdf->Cell(50,6,'Tgl Transaksi',1,0,'C');
        $pdf->Cell(50,6,'Total bayar',1,0,'C');
        $pdf->Cell(60,6,'Nama Pelanggan',1,1,'C');
 
        $pdf->SetFont('Arial','',10);
        $barang= $this->db->query("SELECT * FROM keluar inner join user on keluar.id_user=user.id_user where year(keluar.tgl_keluar)=year(now()) and month(keluar.tgl_keluar)=month(now())")->result();
        $no=1;
        foreach ($barang as $data){
            $pdf->Cell(8,6,$no,1,0);
            $pdf->Cell(30,6,$data->id_keluar,1,0);
            $pdf->Cell(50,6,$data->tgl_keluar,1,0);
            $pdf->Cell(50,6,"Rp ".number_format($data->total_keluar, 0, ".", "."),1,0);
            $pdf->Cell(60,6,$data->nama,1,1);
           
            $no++;
        }
        $pdf->Output();
	

	}
	

	public function cetak_pdf3() {

        $pdf = new FPDF('P', 'mm','Letter');

        $pdf->AddPage();

        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(0,7,'TRANSAKSI BARANG KELUAR',0,1,'C');
        $pdf->Cell(10,7,'',0,1);

        $pdf->SetFont('Arial','B',10);

        $pdf->Cell(8,6,'No',1,0,'C');
        $pdf->Cell(30,6,'Kode Transaksi',1,0,'C');
        $pdf->Cell(50,6,'Tgl Transaksi',1,0,'C');
        $pdf->Cell(50,6,'Total bayar',1,0,'C');
        $pdf->Cell(60,6,'Nama Pelanggan',1,1,'C');
 
        $pdf->SetFont('Arial','',10);
        $barang= $this->db->query("SELECT * FROM keluar inner join user on keluar.id_user=user.id_user where year(keluar.tgl_keluar)=year(now())")->result();
        $no=1;
        foreach ($barang as $data){
            $pdf->Cell(8,6,$no,1,0);
            $pdf->Cell(30,6,$data->id_keluar,1,0);
            $pdf->Cell(50,6,$data->tgl_keluar,1,0);
            $pdf->Cell(50,6,"Rp ".number_format($data->total_keluar, 0, ".", "."),1,0);
            $pdf->Cell(60,6,$data->nama,1,1);
           
            $no++;
        }
        $pdf->Output();
	

	}
	
	public function cetak_pdf4() {

	
		if(isset($_GET['filter']) && ! empty($_GET['filter'])){ // Cek apakah user telah memilih filter dan klik tombol tampilkan
			$filter = $_GET['filter']; // Ambil data filder yang dipilih user
			if($filter == '1'){ // Jika filter nya 1 (per tanggal)
				$tgl_keluar = $_GET['tanggal'];
				$pdf = new FPDF('P', 'mm','Letter');

							$pdf->AddPage();

							$pdf->SetFont('Arial','B',16);
							$pdf->Cell(0,7,'TRANSAKSI BARANG KELUAR',0,1,'C');
							$pdf->Cell(10,7,'',0,1);

							$pdf->SetFont('Arial','B',10);

							$pdf->Cell(8,6,'No',1,0,'C');
							$pdf->Cell(30,6,'Kode Transaksi',1,0,'C');
							$pdf->Cell(50,6,'Tgl Transaksi',1,0,'C');
							$pdf->Cell(50,6,'Total bayar',1,0,'C');
							$pdf->Cell(60,6,'Nama User',1,1,'C');
					
							$pdf->SetFont('Arial','',10);
							$barang= $this->db->query("SELECT * FROM keluar inner join user on keluar.id_user=user.id_user where date(keluar.tgl_keluar)='$tgl_keluar'")->result();
							$no=1;
							foreach ($barang as $data){
								$pdf->Cell(8,6,$no,1,0);
								$pdf->Cell(30,6,$data->id_keluar,1,0);
								$pdf->Cell(50,6,$data->tgl_keluar,1,0);
								$pdf->Cell(50,6,"Rp ".number_format($data->total_keluar, 0, ".", "."),1,0);
								$pdf->Cell(60,6,$data->nama,1,1);
							
								$no++;
							}
							$pdf->Output();
						
			
			}else if($filter == '2'){ // Jika filter nya 2 (per bulan)
				$bulan = $_GET['bulan'];
				$tahun = $_GET['tahun'];
				$pdf = new FPDF('P', 'mm','Letter');

							$pdf->AddPage();

							$pdf->SetFont('Arial','B',16);
							$pdf->Cell(0,7,'TRANSAKSI BARANG keluar',0,1,'C');
							$pdf->Cell(10,7,'',0,1);

							$pdf->SetFont('Arial','B',10);

							$pdf->Cell(8,6,'No',1,0,'C');
							$pdf->Cell(30,6,'Kode Transaksi',1,0,'C');
							$pdf->Cell(50,6,'Tgl Transaksi',1,0,'C');
							$pdf->Cell(50,6,'Total bayar',1,0,'C');
							$pdf->Cell(60,6,'Nama User',1,1,'C');
					
							$pdf->SetFont('Arial','',10);
							$barang= $this->db->query("SELECT * FROM keluar inner join user on keluar.id_user=user.id_user where month(tgl_keluar)=$bulan and year(tgl_keluar)=$tahun")->result();
							$no=1;
							foreach ($barang as $data){
								$pdf->Cell(8,6,$no,1,0);
								$pdf->Cell(30,6,$data->id_keluar,1,0);
								$pdf->Cell(50,6,$data->tgl_keluar,1,0);
								$pdf->Cell(50,6,"Rp ".number_format($data->total_keluar, 0, ".", "."),1,0);
								$pdf->Cell(60,6,$data->nama,1,1);
							
								$no++;
							}
							$pdf->Output();
						
			}else{ // Jika filter nya 3 (per tahun)
				$tahun = $_GET['tahun'];

				$pdf = new FPDF('P', 'mm','Letter');

							$pdf->AddPage();

							$pdf->SetFont('Arial','B',16);
							$pdf->Cell(0,7,'TRANSAKSI BARANG keluar',0,1,'C');
							$pdf->Cell(10,7,'',0,1);

							$pdf->SetFont('Arial','B',10);

							$pdf->Cell(8,6,'No',1,0,'C');
							$pdf->Cell(30,6,'Kode Transaksi',1,0,'C');
							$pdf->Cell(50,6,'Tgl Transaksi',1,0,'C');
							$pdf->Cell(50,6,'Total bayar',1,0,'C');
							$pdf->Cell(60,6,'Nama User',1,1,'C');
					
							$pdf->SetFont('Arial','',10);
							$barang= $this->db->query("SELECT * FROM keluar inner join user on keluar.id_user=user.id_user where year(tgl_keluar)=$tahun")->result();
							$no=1;
							foreach ($barang as $data){
								$pdf->Cell(8,6,$no,1,0);
								$pdf->Cell(30,6,$data->id_keluar,1,0);
								$pdf->Cell(50,6,$data->tgl_keluar,1,0);
								$pdf->Cell(50,6,"Rp ".number_format($data->total_keluar, 0, ".", "."),1,0);
								$pdf->Cell(60,6,$data->nama,1,1);
							
								$no++;
							}
							$pdf->Output();
						
				
			
			}
		}


	}



}