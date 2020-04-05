<?php
class supplier extends CI_Controller {

    //load library, helper, dan model
	function __construct(){  
		parent::__construct(); 
		$this->load->helper('url');
		$this->load->helper('html'); 
        $this->load->model('m_supplier');
        $this->load->model('m_supplier');
        $this->load->library('pagination');
        $this->load->library('upload'); 
		$this->load->helper('form'); 
    }
    
    //fungsi menampilkan data supplier dan halaman
    public function index() 
    {
        //menampilkan header dan sidebar
        $this->load->view('v_header');
        $this->load->view('v_sidebar');
        //konfigurasi url saat klik halaman
        $q = urldecode($this->input->get('q', TRUE));
        $per_page = intval($this->input->get('per_page'));
        if ($q <> '') {
            $config['base_url'] = base_url() . 'supplier/?q=' . urlencode($q);
            $config['first_url'] = base_url() . 'supplier/?q=' . urlencode($q);
        } else {
            $config['base_url'] = base_url() . 'supplier';
            $config['first_url'] = base_url() . 'supplier';
        }
        //konfigurasi banyak row dalam satu halaman
        $config['per_page'] = 10;
        $config['page_query_string'] = TRUE;
        $config['total_rows'] = $this->m_supplier->total_rows($q);
        $supplier = $this->m_supplier->get_limit_data($config['per_page'], $per_page, $q);
        $this->load->library('pagination');
        $this->pagination->initialize($config);
        //menampilkan data
        $data = array(
            'supplier_data' => $supplier,
            'q' => $q,
            'pagination' => $this->pagination->create_links(),
            'total_rows' => $config['total_rows'],
            'per_page' => $per_page,
        );
        //menampilkan view supplier
        $this->load->view('v_supplier', $data);

    } 

    //untuk menampilkan form insert data
    public function create() 
    {
        //menampilkan header dan sidebar
        $this->load->view('v_header');
        $this->load->view('v_sidebar');

        //memanggil value dari form yang diinputkan user
        $data = array(
        'button' => 'Create',
        'action' => site_url('supplier/create_action'),
	    'id_supplier' => set_value('id_supplier'),
	    'nama_supplier' => set_value('nama_supplier'),
	    'alamat' => set_value('alamat'),
        'no_telp' => set_value('no_telp'),

       
    );  
        //menampilkan view tambah supplier
        $this->load->view('v_supplier1', $data);
    }

    //fungsi untuk insert ke database
    public function create_action() 
    {
        //insert dan konfigurasi gambar
        $nmfile = "supplier_".time();
        $config['upload_path'] = './image/barang';
        $config['allowed_types'] = 'jpg|png';
        $config['max_size'] = '20000';
        $config['file_name'] = $nmfile;
        $this->load->library('upload');
        $this->upload->initialize($config);
        $this->upload->do_upload('foto_supplier');
        $result1 = $this->upload->data();
        $result = array('gambar'=>$result1);
        $dfile = $result['gambar']['file_name'];

        //memasukkan data ke database
        $data = array(
		'nama_supplier' => $this->input->post('nama_supplier',TRUE),
		'alamat' => $this->input->post('alamat',TRUE),
        'no_telp' => $this->input->post('no_telp',TRUE),

	    );

        $this->m_supplier->insert($data);
        ?>
        <script type="text/javascript">
			alert('Data Berhasil di Tambahkan');
			window.location = '<?php echo base_url('supplier'); ?>'
        </script>
        <?php
        
    }
    
    //untuk menampilkan data pada form edit
    public function update($id) 
    {
         //menampilkan header dan sidebar
        $this->load->view('v_header');
        $this->load->view('v_sidebar');
        $row = $this->m_supplier->get_by_id($id);
        //menampilkan data ke dalam form
        if ($row) {
            $data = array(
            'button' => 'Update',
            'action' => site_url('supplier/update_action'),
            'id_supplier' => set_value('id_supplier', $row->id_supplier),
            'nama_supplier' => set_value('nama_supplier', $row->nama_supplier),
            'alamat' => set_value('alamat', $row->alamat),
            'no_telp' => set_value('no_telp', $row->no_telp),

            'konten' => 'supplier/supplier_form',
            'judul' => 'Data supplier',
            );
            //menampilkan form edit data
            $this->load->view('v_supplier1', $data);
        } else {
            $this->session->set_flashdata('message', 'Record Not Found');
            redirect(site_url('supplier'));
            }
        }
    //fungsi update data ke database
    public function update_action() 
    {
        //jika gambar tidak diinput oleh user 
        if (empty($_FILES["foto_supplier"]["name"])) {    
            $data = array(
            'nama_supplier' => $this->input->post('nama_supplier',TRUE),
            'alamat' => $this->input->post('alamat',TRUE),
            'no_telp' => $this->input->post('no_telp',TRUE),

            );
            $this->m_supplier->update($this->input->post('id_supplier', TRUE), $data);
            $this->session->set_flashdata('message', 'Update Record Success');
            redirect(site_url('supplier'));
        //jika gambar diinput oleh user
        } else {
            $nmfile = "supplier_".time();
            $config['upload_path'] = './image/supplier';
            $config['allowed_types'] = 'jpg|png';
            $config['max_size'] = '20000';
            $config['file_name'] = $nmfile;
            $this->load->library('upload');
            $this->upload->initialize($config);
            $this->upload->do_upload('foto_supplier');
            $result1 = $this->upload->data();
            $result = array('gambar'=>$result1);
            $dfile = $result['gambar']['file_name'];
            //masukkan data ke database
            $data = array(
            'nama_supplier' => $this->input->post('nama_supplier',TRUE),
            'alamat' => $this->input->post('alamat',TRUE),
            'no_telp' => $this->input->post('no_telp',TRUE),

            );

        $this->m_supplier->update($this->input->post('id_supplier', TRUE), $data);
        ?>
            <script type="text/javascript">
                alert('Data Berhasil di Update');
                window.location = '<?php echo base_url('user'); ?>'
            </script>
            <?php
        }

           
    }
    //fungsi delete data database
    public function delete($id) 
{
    $row = $this->m_supplier->get_by_id($id);

    if ($row) {
        $this->m_supplier->delete($id);
        ?>
        <script type="text/javascript">
			alert('Data Berhasil di Hapus');
			window.location = '<?php echo base_url('supplier'); ?>'
        </script>
        <?php
    }
}
    
}
    ?>


