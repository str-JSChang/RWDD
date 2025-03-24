/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package ppemanager.util;

import java.io.File;
import java.io.FileWriter;
import java.io.IOException;
import java.io.PrintWriter;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.util.ArrayList;
import java.util.List;
import java.util.Scanner;
import ppemanager.model.Hospital;
import ppemanager.model.PPE_Item;
import ppemanager.model.Supplier;
import ppemanager.model.Transaction;
import ppemanager.model.User;
import ppemanager.model.UserType;

/**
 *
 * @author Jason
 */
public class fileHandler {
    // File paths
    private static final String USERS_FILE = ".\\src\\ppemanager\\model\\users.txt";
    private static final String PPE_FILE = ".\\src\\ppemanager\\model\\ppe.txt";
    private static final String SUPPLIERS_FILE = ".\\src\\ppemanager\\model\\suppliers.txt";
    private static final String HOSPITALS_FILE = ".\\src\\ppemanager\\model\\hospitals.txt";
    private static final String TRANSACTIONS_FILE = ".\\src\\ppemanager\\model\\transactions.txt";
    
    // DateTimeFormatter for handling transaction dates
    private static final DateTimeFormatter DATE_FORMATTER = DateTimeFormatter.ISO_LOCAL_DATE_TIME;
    
    // Check if first-time setup is nit
    public static boolean isFirstTimeSetup() {
        File ppeFile = new File(PPE_FILE);
        return !ppeFile.exists();
    }
    
    // Initialize the system with default data
    public static void initializeSystem() throws IOException {
        // Create initial PPE items (100 boxes each)
        List<PPE_Item> initialItems = new ArrayList<>();
        initialItems.add(new PPE_Item("HC", "SUP1", 100, "Head Cover"));
        initialItems.add(new PPE_Item("FS", "SUP1", 100, "Face Shield"));
        initialItems.add(new PPE_Item("MS", "SUP2", 100, "Mask"));
        initialItems.add(new PPE_Item("GL", "SUP2", 100, "Gloves"));
        initialItems.add(new PPE_Item("GW", "SUP3", 100, "Gown"));
        initialItems.add(new PPE_Item("SC", "SUP3", 100, "Shoe Covers"));
        
        // Create initial suppliers
        List<Supplier> initialSuppliers = new ArrayList<>();
        initialSuppliers.add(new Supplier("SUP1", "Medical Supplies Co."));
        initialSuppliers.add(new Supplier("SUP2", "Healthcare Equipment Ltd."));
        initialSuppliers.add(new Supplier("SUP3", "PPE Manufacturers Inc."));
        
        // Create initial hospitals
        List<Hospital> initialHospitals = new ArrayList<>();
        initialHospitals.add(new Hospital("HOS1", "General Hospital"));
        initialHospitals.add(new Hospital("HOS2", "Community Medical Center"));
        initialHospitals.add(new Hospital("HOS3", "Regional Medical Center"));
        
        // Create admin user
        List<User> initialUsers = new ArrayList<>();
        initialUsers.add(new User("admin1", "Administrator", "admin123", UserType.ADMIN));
        initialUsers.add(new User("staff1", "Staff Member 1", "staff123", UserType.STAFF));
        initialUsers.add(new User("staff2", "Staff Member 2", "staff456", UserType.STAFF));
        
        // TODO:AKJSDFLASDIJFL;KASDJFOLIASDJLF
        // Create initial Transactions
//        List<Transactions> initialTransactions = new Arraylist<>();
//        initialTransactions.add(new Transaction("T0001","2025-03-15T10:30:00","MS","SUP2","+","50","100","150"));
//        initialTransactions.add(new Transaction("T0002","2025-03-15T11:45:00","MS","SUP2","+","50","100","150"));
//        initialTransactions.add(new Transaction("T0003","2025-03-15T14:20:00","MS","SUP2","+","50","100","150"));
//        initialTransactions.add(new Transaction("T0004","2025-03-16T09:15:00","MS","SUP2","+","50","100","150"));
//        initialTransactions.add(new Transaction("T0005","2025-03-16T15:40:00","MS","SUP2","+","50","100","150"));
        
        // Save initial data to files
        savePPEItems(initialItems);
        saveSuppliers(initialSuppliers);
        saveHospitals(initialHospitals);
        saveUsers(initialUsers);
        
        // TODO: KJDSAFGLKJSDFKJGSDLF;IKJG;LSDKFJGL
        // Create empty transactions file
        new File(TRANSACTIONS_FILE).createNewFile();
    }
    
    // ==================== USER OPERATIONS ====================
    
    public static List<User> loadUsers() throws IOException {
        List<User> users = new ArrayList<>();
        File file = new File(USERS_FILE);
        
        if (!file.exists()) {
            return users;
        }
        
        try (Scanner scanner = new Scanner(file)) {
            while (scanner.hasNextLine()) {
                String line = scanner.nextLine();
                String[] parts = line.split(",");
                if (parts.length >= 4) {
                    String userId = parts[0];
                    String name = parts[1];
                    String password = parts[2];
                    UserType userType = UserType.valueOf(parts[3]);
                    
                    users.add(new User(userId, name, password, userType));
                }
            }
        }
        
        return users;
    }
    
    public static void saveUsers(List<User> users) throws IOException {
        try (PrintWriter writer = new PrintWriter(new FileWriter(USERS_FILE))) {
            for (User user : users) {
                writer.println(user.getUserId() + "," + 
                              user.getName() + "," + 
                              user.getPassword() + "," + 
                              user.getUserType());
            }
        }
    }
    
    // ==================== PPE ITEM OPERATIONS ====================
    
    public static List<PPE_Item> loadPPEItems() throws IOException {
        List<PPE_Item> items = new ArrayList<>();
        File file = new File(PPE_FILE);
        
        if (!file.exists()) {
            return items;
        }
        
        try (Scanner scanner = new Scanner(file)) {
            while (scanner.hasNextLine()) {
                String line = scanner.nextLine();
                String[] parts = line.split(",");
                if (parts.length >= 4) {
                    String itemCode = parts[0];
                    String supplierCode = parts[1];
                    int quantity = Integer.parseInt(parts[2]);
                    String itemName = parts[3];
                    
                    items.add(new PPE_Item(itemCode, supplierCode, quantity, itemName));
                }
            }
        }
        
        return items;
    }
    
    public static void savePPEItems(List<PPE_Item> items) throws IOException {
        try (PrintWriter writer = new PrintWriter(new FileWriter(PPE_FILE))) {
            for (PPE_Item item : items) {
                writer.println(item.getItemCode() + "," + 
                              item.getSupplierCode() + "," + 
                              item.getQuantity() + "," + 
                              item.getItemName());
            }
        }
    }
    
    // ==================== SUPPLIER OPERATIONS ====================
    
    public static List<Supplier> loadSuppliers() throws IOException {
        List<Supplier> suppliers = new ArrayList<>();
        File file = new File(SUPPLIERS_FILE);
        
        if (!file.exists()) {
            return suppliers;
        }
        
        try (Scanner scanner = new Scanner(file)) {
            while (scanner.hasNextLine()) {
                String line = scanner.nextLine();
                String[] parts = line.split(",");
                if (parts.length >= 2) {
                    String supplierCode = parts[0];
                    String name = parts[1];
                    
                    suppliers.add(new Supplier(supplierCode, name));
                }
            }
        }
        
        return suppliers;
    }
    
    public static void saveSuppliers(List<Supplier> suppliers) throws IOException {
        try (PrintWriter writer = new PrintWriter(new FileWriter(SUPPLIERS_FILE))) {
            for (Supplier supplier : suppliers) {
                writer.println(supplier.getSupplierCode() + "," + 
                              supplier.getName());
            }
        }
    }
    
    // ==================== HOSPITAL OPERATIONS ====================
    
    public static List<Hospital> loadHospitals() throws IOException {
        List<Hospital> hospitals = new ArrayList<>();
        File file = new File(HOSPITALS_FILE);
        
        if (!file.exists()) {
            return hospitals;
        }
        
        try (Scanner scanner = new Scanner(file)) {
            while (scanner.hasNextLine()) {
                String line = scanner.nextLine();
                String[] parts = line.split(",");
                if (parts.length >= 2) {
                    String hospitalCode = parts[0];
                    String name = parts[1];
                    
                    hospitals.add(new Hospital(hospitalCode, name));
                }
            }
        }
        
        return hospitals;
    }
    
    public static void saveHospitals(List<Hospital> hospitals) throws IOException {
        try (PrintWriter writer = new PrintWriter(new FileWriter(HOSPITALS_FILE))) {
            for (Hospital hospital : hospitals) {
                writer.println(hospital.getHospitalCode() + "," + 
                              hospital.getName());
            }
        }
    }
    
// ==================== TRANSACTION OPERATIONS ====================
    
    public static List<Transaction> loadTransactions() throws IOException {
        List<Transaction> transactions = new ArrayList<>();
        File file = new File(TRANSACTIONS_FILE);

        if (!file.exists()) {
            return transactions;
        }

        try (Scanner scanner = new Scanner(file)) {
            while (scanner.hasNextLine()) {
                String line = scanner.nextLine();
                // Skip comment lines
                if (line.trim().startsWith("//")) {
                    continue;
                }

                String[] parts = line.split(",");
                if (parts.length >= 8) {
                    String transactionId = parts[0];
                    LocalDateTime transactionDate = LocalDateTime.parse(parts[1], DATE_FORMATTER);
                    String itemCode = parts[2];
                    String entityCode = parts[3];
                    String actionType = parts[4];  // "+" or "-"
                    int quantity = Integer.parseInt(parts[5]);
                    int originalQuantity = Integer.parseInt(parts[6]);
                    int newQuantity = Integer.parseInt(parts[7]);

                    transactions.add(new Transaction(
                            transactionId, transactionDate, itemCode, entityCode, 
                            actionType, quantity, originalQuantity, newQuantity));
                }
            }
        } catch (Exception e) {
            System.err.println("Error loading transactions: " + e.getMessage());
            throw new IOException("Failed to load transactions: " + e.getMessage(), e);
        }

        return transactions;
    }

    public static void saveTransactions(List<Transaction> transactions) throws IOException {
        try (PrintWriter writer = new PrintWriter(new FileWriter(TRANSACTIONS_FILE))) {
            writer.println("// transactions.txt");
            writer.println("// Format: transactionId,transactionDate,itemCode,entityCode,actionType,quantity,originalQuantity,newQuantity");
            writer.println("// actionType: '+' for receiving from suppliers, '-' for distributing to hospitals");
            for (Transaction transaction : transactions) {
                writer.println(transaction.getTransactionId() + "," + 
                               transaction.getTransactionDate().format(DATE_FORMATTER) + "," + 
                               transaction.getItemCode() + "," + 
                               transaction.getEntityCode() + "," + 
                               transaction.getActionType() + "," + 
                               transaction.getQuantity() + "," + 
                               transaction.getOriginalQuantity() + "," + 
                               transaction.getNewQuantity());
            }
        } catch (Exception e) {
            System.err.println("Error saving transactions: " + e.getMessage());
            throw new IOException("Failed to save transactions: " + e.getMessage(), e);
        }
    }

    public static void addTransaction(Transaction transaction) throws IOException {
        try (PrintWriter writer = new PrintWriter(new FileWriter(TRANSACTIONS_FILE, true))) {
            // If the file is empty, add the header
            File file = new File(TRANSACTIONS_FILE);
            if (file.length() == 0) {
                writer.println("// transactions.txt");
                writer.println("// Format: transactionId,transactionDate,itemCode,entityCode,actionType,quantity,originalQuantity,newQuantity");
                writer.println("// actionType: '+' for receiving from suppliers, '-' for distributing to hospitals");
            }

            writer.println(transaction.getTransactionId() + "," + 
                           transaction.getTransactionDate().format(DATE_FORMATTER) + "," + 
                           transaction.getItemCode() + "," + 
                           transaction.getEntityCode() + "," + 
                           transaction.getActionType() + "," + 
                           transaction.getQuantity() + "," + 
                           transaction.getOriginalQuantity() + "," + 
                           transaction.getNewQuantity());
        } catch (Exception e) {
            System.err.println("Error adding transaction: " + e.getMessage());
            throw new IOException("Failed to add transaction: " + e.getMessage(), e);
        }
    }





}